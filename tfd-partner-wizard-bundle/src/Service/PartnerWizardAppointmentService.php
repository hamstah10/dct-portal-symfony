<?php

namespace Tfd\PartnerWizardBundle\Service;

use App\Entity\SuperadminCalendarEvent;
use Doctrine\ORM\EntityManagerInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardLead;

/**
 * Freie Beratungstermine für den Partner-Wizard.
 *
 * Arbeitszeiten, Dauer usw. kommen aus dem Client. Belegt ist alles, was im Superadmin-Kalender
 * (gleicher calendarId) eingetragen ist – egal ob von Hand angelegt oder über den Wizard gebucht.
 */
final class PartnerWizardAppointmentService
{
    public const SOURCE = 'partner_wizard';

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return array<string, mixed> */
    public function getSettings(PartnerWizardClient $client): array
    {
        return [
            'enabled' => $client->isAppointmentEnabled(),
            'required' => $client->isAppointmentEnabled() && $client->isAppointmentRequired(),
            'title' => $client->getAppointmentTitle(),
            'durationMinutes' => $client->getAppointmentDurationMinutes(),
            'timezone' => $client->getAppointmentTimezone(),
            'daysAhead' => $client->getAppointmentDaysAhead(),
        ];
    }

    /**
     * Freie Slots, gruppiert nach Tag (nur Tage mit mindestens einem Slot).
     *
     * @return list<array{date: string, weekday: int, slots: list<array{start: string, end: string, time: string}>}>
     */
    public function getFreeSlots(PartnerWizardClient $client, ?\DateTimeImmutable $from = null, ?int $days = null): array
    {
        $zone = new \DateTimeZone($client->getAppointmentTimezone());
        $now = new \DateTimeImmutable('now', $zone);
        $earliest = $now->modify(sprintf('+%d hours', $client->getAppointmentMinNoticeHours()));
        $lastDay = $now->setTime(0, 0)->modify(sprintf('+%d days', $client->getAppointmentDaysAhead()));

        $firstDay = ($from ?? $now)->setTimezone($zone)->setTime(0, 0);
        if ($firstDay < $now->setTime(0, 0)) {
            $firstDay = $now->setTime(0, 0);
        }
        $days = max(1, min($days ?? $client->getAppointmentDaysAhead(), 62));
        $endDay = $firstDay->modify(sprintf('+%d days', $days));
        if ($endDay > $lastDay->modify('+1 day')) {
            $endDay = $lastDay->modify('+1 day');
        }
        if ($endDay <= $firstDay) {
            return [];
        }

        $duration = $client->getAppointmentDurationMinutes();
        $buffer = $client->getAppointmentBufferMinutes();
        $weekdays = $client->getAppointmentWeekdays();
        [$startH, $startM] = array_map('intval', explode(':', $client->getAppointmentDayStart()));
        [$endH, $endM] = array_map('intval', explode(':', $client->getAppointmentDayEnd()));

        $busy = $this->loadBusyIntervals($client, $firstDay, $endDay, $buffer);

        $result = [];
        for ($day = $firstDay; $day < $endDay; $day = $day->modify('+1 day')) {
            if (!in_array((int) $day->format('N'), $weekdays, true)) {
                continue;
            }

            $dayStart = $day->setTime($startH, $startM);
            $dayEnd = $day->setTime($endH, $endM);
            $slots = [];

            for ($slotStart = $dayStart; $slotStart->modify(sprintf('+%d minutes', $duration)) <= $dayEnd; $slotStart = $slotStart->modify(sprintf('+%d minutes', $duration))) {
                $slotEnd = $slotStart->modify(sprintf('+%d minutes', $duration));
                if ($slotStart < $earliest || $this->overlapsBusy($slotStart, $slotEnd, $busy)) {
                    continue;
                }

                $slots[] = [
                    'start' => $slotStart->format(\DATE_ATOM),
                    'end' => $slotEnd->format(\DATE_ATOM),
                    'time' => $slotStart->format('H:i'),
                ];
            }

            if ($slots !== []) {
                $result[] = [
                    'date' => $day->format('Y-m-d'),
                    'weekday' => (int) $day->format('N'),
                    'slots' => $slots,
                ];
            }
        }

        return $result;
    }

    /** Prüft, ob genau dieser Start aktuell ein freier Slot ist. Gibt [start, end] zurück oder null. */
    public function resolveFreeSlot(PartnerWizardClient $client, string $start): ?array
    {
        try {
            $requested = new \DateTimeImmutable($start);
        } catch (\Exception) {
            return null;
        }

        $zone = new \DateTimeZone($client->getAppointmentTimezone());
        $day = $requested->setTimezone($zone)->setTime(0, 0);

        foreach ($this->getFreeSlots($client, $day, 1) as $group) {
            foreach ($group['slots'] as $slot) {
                $slotStart = new \DateTimeImmutable($slot['start']);
                if ($slotStart->getTimestamp() === $requested->getTimestamp()) {
                    return [$slotStart, new \DateTimeImmutable($slot['end'])];
                }
            }
        }

        return null;
    }

    /** Legt den Kalendereintrag für einen Lead an (ohne flush). */
    public function createCalendarEvent(PartnerWizardClient $client, PartnerWizardLead $lead, \DateTimeImmutable $start, \DateTimeImmutable $end, array $payload): SuperadminCalendarEvent
    {
        $serverZone = new \DateTimeZone(date_default_timezone_get());
        $contact = is_array($payload['contact'] ?? null) ? $payload['contact'] : [];

        $who = $lead->getName();
        if ($lead->getCompany()) {
            $who .= ' (' . $lead->getCompany() . ')';
        }

        $lines = [
            'Partner-Wizard: ' . $client->getName() . ' (' . $client->getClientId() . ')',
            'Name: ' . $lead->getName(),
        ];
        if ($lead->getCompany()) {
            $lines[] = 'Firma: ' . $lead->getCompany();
        }
        $lines[] = 'E-Mail: ' . $lead->getEmail();
        if ($lead->getPhone()) {
            $lines[] = 'Telefon: ' . $lead->getPhone();
        }
        if ($lead->getRecommendedPackageTitle()) {
            $lines[] = 'Empfohlenes Paket: ' . $lead->getRecommendedPackageTitle();
        }
        $toolNames = array_values(array_filter(array_map(
            static fn (mixed $tool): string => is_array($tool) ? (string) ($tool['name'] ?? '') : '',
            $lead->getRecommendedTools()
        )));
        if ($toolNames !== []) {
            $lines[] = 'Empfohlene Tools: ' . implode(', ', $toolNames);
        }
        $message = trim((string) ($contact['message'] ?? $payload['message'] ?? ''));
        if ($message !== '') {
            $lines[] = '';
            $lines[] = $message;
        }
        $lines[] = '';
        $lines[] = 'Lead #' . $lead->getId();

        $event = (new SuperadminCalendarEvent())
            ->setTitle(mb_substr($client->getAppointmentTitle() . ': ' . $who, 0, 190))
            ->setDescription(implode("\n", $lines))
            ->setLocation($client->getAppointmentLocation())
            ->setGuests([$lead->getEmail()])
            ->setCalendarId($client->getAppointmentCalendarId())
            ->setAllDay(false)
            ->setStartsAt($start->setTimezone($serverZone))
            ->setEndsAt($end->setTimezone($serverZone))
            ->setExternalSource(self::SOURCE)
            ->setExternalUid((string) $lead->getId())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($event);

        return $event;
    }

    /** @return list<array{0: int, 1: int}> Unix-Zeitstempel inkl. Puffer */
    private function loadBusyIntervals(PartnerWizardClient $client, \DateTimeImmutable $from, \DateTimeImmutable $to, int $buffer): array
    {
        $serverZone = new \DateTimeZone(date_default_timezone_get());

        /** @var list<SuperadminCalendarEvent> $events */
        $events = $this->em->createQueryBuilder()
            ->select('e')
            ->from(SuperadminCalendarEvent::class, 'e')
            ->where('e.calendarId = :calendar')
            ->andWhere('e.startsAt < :to')
            ->andWhere('e.endsAt > :from')
            ->setParameter('calendar', $client->getAppointmentCalendarId())
            ->setParameter('from', $from->modify('-1 day')->setTimezone($serverZone))
            ->setParameter('to', $to->modify('+1 day')->setTimezone($serverZone))
            ->getQuery()
            ->getResult();

        $busy = [];
        foreach ($events as $event) {
            if ($event->isAllDay()) {
                // Ganztägig: kompletter Kalendertag (in der Client-Zeitzone) ist belegt
                $zone = new \DateTimeZone($client->getAppointmentTimezone());
                $start = $event->getStartsAt()->setTimezone($zone)->setTime(0, 0);
                $end = $event->getEndsAt()->setTimezone($zone)->setTime(0, 0)->modify('+1 day');
                $busy[] = [$start->getTimestamp(), $end->getTimestamp()];
                continue;
            }

            $busy[] = [
                $event->getStartsAt()->getTimestamp() - $buffer * 60,
                max($event->getEndsAt()->getTimestamp(), $event->getStartsAt()->getTimestamp()) + $buffer * 60,
            ];
        }

        return $busy;
    }

    /** @param list<array{0: int, 1: int}> $busy */
    private function overlapsBusy(\DateTimeImmutable $start, \DateTimeImmutable $end, array $busy): bool
    {
        $s = $start->getTimestamp();
        $e = $end->getTimestamp();
        foreach ($busy as [$bs, $be]) {
            if ($s < $be && $e > $bs) {
                return true;
            }
        }

        return false;
    }
}
