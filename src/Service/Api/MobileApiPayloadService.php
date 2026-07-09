<?php

namespace App\Service\Api;

use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Entity\SupportTicket;
use App\Entity\SupportTicketMessage;
use App\Entity\User;

final class MobileApiPayloadService
{
    /**
     * @return array{id:int,email:string,name:string}
     */
    public function normalizeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail() ?? '',
            'name' => $this->composeUserName($user),
        ];
    }

    public function composeUserName(User $user): string
    {
        $name = trim(sprintf('%s %s', $user->getVorname() ?? '', $user->getNachname() ?? ''));

        return '' !== $name ? $name : (string) $user->getEmail();
    }

    /**
     * @return array{id:int,status:?string,customerEmail:?string,fileName:?string,vehicleDisplay:?string,manufacturer:?string,model:?string,engine:?string,stageLabel:?string,tuningMethod:?string,createdAt:?string,customerId:?int,customerName:?string}
     */
    public function normalizeOrder(Auftrag $auftrag): array
    {
        $kunde = $auftrag->getKunde();

        return [
            'id' => $auftrag->getId(),
            'status' => $auftrag->getStatus(),
            'customerEmail' => $auftrag->getCustomerEmail(),
            'fileName' => $auftrag->getFileName(),
            'vehicleDisplay' => $auftrag->getVehicleDisplay(),
            'manufacturer' => $auftrag->getManufacturer(),
            'model' => $auftrag->getModel(),
            'engine' => $auftrag->getEngine(),
            'stageLabel' => $auftrag->getStageLabel(),
            'tuningMethod' => $auftrag->getTuningMethod(),
            'createdAt' => $auftrag->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'customerId' => $kunde?->getId(),
            'customerName' => $kunde instanceof Kunde ? $this->composeCustomerName($kunde) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeCustomer(Kunde $kunde): array
    {
        return [
            'id' => $kunde->getId(),
            'kundennummer' => $kunde->getKundennummer(),
            'name' => $this->composeCustomerName($kunde),
            'email' => $kunde->getEmail(),
            'firma' => $kunde->getFirma()?->getName(),
            'credits' => $kunde->getCredits(),
            'gesperrt' => $kunde->isGesperrt(),
            'createdAt' => $kunde->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'vorname' => $kunde->getVorname(),
            'nachname' => $kunde->getNachname(),
            'firmenname' => $kunde->getFirmenname(),
            'telefon' => $kunde->getTelefon(),
            'strasse' => $kunde->getStrasse(),
            'plz' => $kunde->getPlz(),
            'stadt' => $kunde->getStadt(),
            'land' => $kunde->getLand(),
            'website' => $kunde->getWebsite(),
            'orderCount' => $kunde->getAuftraege()->count(),
        ];
    }

    public function composeCustomerName(Kunde $kunde): string
    {
        $name = trim(sprintf('%s %s', $kunde->getVorname() ?? '', $kunde->getNachname() ?? ''));

        return '' !== $name ? $name : ($kunde->getFirmenname() ?? ('Kunde #' . $kunde->getId()));
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeTicket(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->getId(),
            'ticketNumber' => $ticket->getTicketNumber(),
            'subject' => $ticket->getSubject(),
            'status' => $ticket->getStatus(),
            'priority' => $ticket->getPriority(),
            'messageCount' => $ticket->getMessageCount(),
            'lastMessagePreview' => $ticket->getLastMessagePreview(),
            'lastMessageAt' => $ticket->getLastMessageAt()?->format(\DateTimeInterface::ATOM),
            'createdAt' => $ticket->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $ticket->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'customerId' => $ticket->getKunde()?->getId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeTicketDetail(SupportTicket $ticket): array
    {
        $kunde = $ticket->getKunde();

        return [
            ...$this->normalizeTicket($ticket),
            'customerName' => $kunde instanceof Kunde ? $this->composeCustomerName($kunde) : null,
            'customerEmail' => $kunde?->getEmail(),
            'messages' => array_map(
                fn (SupportTicketMessage $message): array => $this->normalizeTicketMessage($message),
                $ticket->getMessages()->toArray()
            ),
        ];
    }

    /**
     * @return array{id:int,sender:?string,senderName:?string,message:?string,createdAt:?string}
     */
    public function normalizeTicketMessage(SupportTicketMessage $message): array
    {
        return [
            'id' => $message->getId(),
            'sender' => $message->getSender(),
            'senderName' => $message->getSenderName(),
            'message' => $message->getMessage(),
            'createdAt' => $message->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
