<?php

namespace App\Twig\Components;

use App\Entity\SupportEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('support_email_search', template: 'components/support_email_search.html.twig')]
final class SupportEmailSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    #[LiveProp]
    public ?int $accountId = null;

    #[LiveProp]
    public ?string $folder = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   meta:?string,
     *   badge:?string,
     *   badgeTone:?string
     * }>
     */
    public function getResults(): array
    {
        $query = trim($this->query);
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $term = '%' . $normalized . '%';

        $qb = $this->entityManager->createQueryBuilder()
            ->select('m', 'k')
            ->from(SupportEmailMessage::class, 'm')
            ->leftJoin('m.emailKonto', 'k')
            ->orderBy('m.receivedAt', 'DESC')
            ->addOrderBy('m.sentAt', 'DESC')
            ->addOrderBy('m.updatedAt', 'DESC')
            ->setMaxResults(max(1, $this->limit));

        if (null !== $this->accountId && $this->accountId > 0) {
            $qb
                ->andWhere('k.id = :accountId')
                ->setParameter('accountId', $this->accountId);
        }

        if (null !== $this->folder && '' !== $this->folder && 'all' !== $this->folder) {
            $qb
                ->andWhere('m.folder = :folder')
                ->setParameter('folder', $this->folder);
        }

        $qb
            ->andWhere(
                'LOWER(COALESCE(m.subject, \'\')) LIKE :term
                 OR LOWER(COALESCE(m.fromName, \'\')) LIKE :term
                 OR LOWER(COALESCE(m.fromEmail, \'\')) LIKE :term
                 OR LOWER(COALESCE(m.toEmail, \'\')) LIKE :term
                 OR LOWER(COALESCE(m.bodyText, \'\')) LIKE :term'
            )
            ->setParameter('term', $term);

        $items = $qb->getQuery()->getResult();

        $results = [];
        foreach ($items as $message) {
            if (!$message instanceof SupportEmailMessage) {
                continue;
            }

            $results[] = [
                'url' => $this->buildMessageUrl($message),
                'label' => $this->buildLabel($message),
                'subLabel' => $this->buildSubLabel($message),
                'meta' => $this->formatDate($message->getReceivedAt() ?? $message->getSentAt()),
                'badge' => $this->folderLabel($message->getFolder()),
                'badgeTone' => $message->isRead() ? 'secondary' : 'primary',
            ];
        }

        return $results;
    }

    private function buildMessageUrl(SupportEmailMessage $message): string
    {
        $params = [
            'message' => $message->getId(),
        ];

        $account = $message->getEmailKonto();
        if ($account && $account->getId()) {
            $params['account'] = $account->getId();
        } elseif (null !== $this->accountId && $this->accountId > 0) {
            $params['account'] = $this->accountId;
        }

        $folder = $message->getFolder();
        if ('' !== $folder) {
            $params['folder'] = $folder;
        } elseif (null !== $this->folder && '' !== $this->folder) {
            $params['folder'] = $this->folder;
        }

        return $this->urlGenerator->generate('admin_support_email', $params);
    }

    private function buildLabel(SupportEmailMessage $message): string
    {
        $subject = trim($message->getSubject());
        if ('' !== $subject) {
            return $subject;
        }

        return 'E-Mail #' . (string) ($message->getId() ?? '');
    }

    private function buildSubLabel(SupportEmailMessage $message): ?string
    {
        $fromName = trim((string) ($message->getFromName() ?? ''));
        $fromEmail = trim((string) ($message->getFromEmail() ?? ''));

        if ('' !== $fromName && '' !== $fromEmail) {
            return sprintf('%s <%s>', $fromName, $fromEmail);
        }
        if ('' !== $fromName) {
            return $fromName;
        }
        if ('' !== $fromEmail) {
            return $fromEmail;
        }

        $toEmail = trim((string) ($message->getToEmail() ?? ''));
        return '' !== $toEmail ? $toEmail : null;
    }

    private function formatDate(?\DateTimeImmutable $date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date->format('d.m.Y H:i');
    }

    private function folderLabel(string $folder): string
    {
        return match ($folder) {
            SupportEmailMessage::FOLDER_INBOX => 'Inbox',
            SupportEmailMessage::FOLDER_SENT => 'Sent',
            SupportEmailMessage::FOLDER_DRAFT => 'Draft',
            SupportEmailMessage::FOLDER_SPAM => 'Spam',
            SupportEmailMessage::FOLDER_TRASH => 'Trash',
            SupportEmailMessage::FOLDER_ARCHIVE => 'Archive',
            default => ucfirst($folder),
        };
    }
}
