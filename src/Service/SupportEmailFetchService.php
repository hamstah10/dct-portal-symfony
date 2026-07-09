<?php

namespace App\Service;

use App\Entity\EmailKonto;
use App\Entity\SupportEmailMessage;
use App\Repository\SupportEmailMessageRepository;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Webklex\PHPIMAP\Address as ImapAddress;
use Webklex\PHPIMAP\Attribute as ImapAttribute;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;

final class SupportEmailFetchService
{
    private const DEFAULT_LIMIT = 100;
    private const MAX_LIMIT = 500;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupportEmailMessageRepository $supportEmailMessageRepository,
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
    ) {
    }

    /**
     * @return array{
     *   accountId:int,
     *   accountLabel:string,
     *   fetched:int,
     *   created:int,
     *   updated:int,
     *   failed:int,
     *   error:string|null
     * }
     */
    public function fetchAccount(EmailKonto $account, ?int $limit = null): array
    {
        $stats = [
            'accountId' => (int) ($account->getId() ?? 0),
            'accountLabel' => $this->buildAccountLabel($account),
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'error' => null,
        ];

        if (!$account->isActive()) {
            $stats['error'] = 'Konto ist inaktiv.';

            return $stats;
        }
        if (!$account->isFetchEnabled()) {
            $stats['error'] = 'Abruf ist fuer das Konto deaktiviert.';

            return $stats;
        }

        $username = trim((string) ($account->getImapUsername() ?: $account->getEmailAddress()));
        $password = trim((string) ($account->getImapPassword() ?? ''));
        $folderName = trim($account->getImapFolder());
        $imapConfig = $this->resolveImapConnectionConfig($account);
        $host = $imapConfig['host'];
        $port = $imapConfig['port'];
        if ('' === $folderName) {
            $folderName = 'INBOX';
        }

        if ('' === $host || '' === $username || '' === $password) {
            $stats['error'] = 'IMAP Konfiguration ist unvollstaendig.';

            return $stats;
        }

        $fetchLimit = (int) ($limit ?? self::DEFAULT_LIMIT);
        $fetchLimit = max(1, min(self::MAX_LIMIT, $fetchLimit));

        $client = $this->createClient($host, $port, $imapConfig['encryption'], $username, $password);
        $newIncomingMessages = [];

        try {
            $client->connect();

            $folder = $this->resolveFolder($client, $folderName);
            if (!$folder instanceof Folder) {
                $stats['error'] = sprintf('IMAP-Ordner "%s" nicht gefunden.', $folderName);

                return $stats;
            }

            $messages = $folder
                ->messages()
                ->all()
                ->leaveUnread()
                ->setFetchOrder('desc')
                ->limit($fetchLimit)
                ->get();

            foreach ($messages as $imapMessage) {
                if (!$imapMessage instanceof Message) {
                    continue;
                }

                try {
                    $externalMessageId = $this->resolveExternalMessageId($imapMessage);
                    $entity = $this->supportEmailMessageRepository->findOneBy([
                        'emailKonto' => $account,
                        'externalMessageId' => $externalMessageId,
                    ]);
                    $isNewEntity = !$entity instanceof SupportEmailMessage;

                    if (!$isNewEntity) {
                        ++$stats['updated'];
                    } else {
                        $entity = (new SupportEmailMessage())
                            ->setEmailKonto($account)
                            ->setExternalMessageId($externalMessageId);
                        $this->entityManager->persist($entity);
                        ++$stats['created'];
                    }

                    $this->hydrateEntity($entity, $imapMessage, $folder->path);
                    if (
                        !$isNewEntity
                        || SupportEmailMessage::FOLDER_INBOX !== $entity->getFolder()
                        || $entity->isDraft()
                    ) {
                        ++$stats['fetched'];

                        continue;
                    }

                    $newIncomingMessages[] = $entity;
                    ++$stats['fetched'];
                } catch (\Throwable) {
                    ++$stats['failed'];
                }
            }

            $this->entityManager->flush();
            $this->publishIncomingEmailNotifications($newIncomingMessages, $account);
        } catch (\Throwable $throwable) {
            $stats['error'] = sprintf(
                '%s:%d - %s',
                $host,
                $port,
                $this->truncateMessage($this->flattenThrowableMessage($throwable))
            );
        } finally {
            try {
                $client->disconnect();
            } catch (\Throwable) {
            }
        }

        return $stats;
    }

    /**
     * @param list<SupportEmailMessage> $messages
     */
    private function publishIncomingEmailNotifications(array $messages, EmailKonto $account): void
    {
        if ([] === $messages) {
            return;
        }

        $topicSet = $this->portalMercureTopics->adminNotifications();
        foreach (array_slice($messages, 0, 5) as $message) {
            $summary = sprintf('Neue Support-E-Mail: %s', $message->getSubject() ?: 'Ohne Betreff');

            $this->portalRealtimePublisher->publish($topicSet, $summary, [
                'body' => $this->buildSupportEmailNotificationBody($message, $account),
                'tag' => sprintf('support-email-%d', (int) $message->getId()),
            ]);
        }

        if (\count($messages) > 5) {
            $this->portalRealtimePublisher->publish(
                $topicSet,
                sprintf('%d neue Support-E-Mails', \count($messages)),
                [
                    'body' => sprintf('Im Konto %s sind weitere neue Nachrichten eingegangen.', $this->buildAccountLabel($account)),
                    'tag' => sprintf('support-email-batch-%d', (int) ($account->getId() ?? 0)),
                ],
            );
        }
    }

    private function buildSupportEmailNotificationBody(SupportEmailMessage $message, EmailKonto $account): string
    {
        $bodyParts = [];

        $sender = trim(implode(' ', array_filter([
            trim((string) ($message->getFromName() ?? '')),
            trim((string) ($message->getFromEmail() ?? '')),
        ])));
        if ('' !== $sender) {
            $bodyParts[] = $sender;
        }

        $accountLabel = $this->buildAccountLabel($account);
        if ('' !== $accountLabel) {
            $bodyParts[] = sprintf('Konto %s', $accountLabel);
        }

        $body = implode(' | ', $bodyParts);

        return '' !== $body ? $body : 'Neue Nachricht im Support-Postfach.';
    }

    private function createClient(string $host, int $port, string|bool $encryption, string $username, string $password): Client
    {
        $manager = new ClientManager([
            'default' => 'support',
            'accounts' => [
                'support' => [
                    'host' => $host,
                    'port' => $port,
                    'protocol' => 'imap',
                    'encryption' => $encryption,
                    'validate_cert' => false,
                    'username' => $username,
                    'password' => $password,
                    'authentication' => null,
                    'timeout' => 30,
                ],
            ],
            'options' => [
                'delimiter' => '/',
                'fetch' => IMAP::FT_PEEK,
                'sequence' => IMAP::ST_UID,
                'fetch_body' => true,
                'fetch_flags' => true,
                'soft_fail' => true,
                'rfc822' => true,
                'debug' => false,
                'uid_cache' => true,
                'message_key' => 'uid',
                'fetch_order' => 'desc',
                'open' => [
                    'DISABLE_AUTHENTICATOR' => 'GSSAPI',
                ],
            ],
        ]);

        return $manager->account('support');
    }

    private function resolveFolder(Client $client, string $folderName): ?Folder
    {
        $search = strtolower(trim($folderName));
        if ('' === $search) {
            $search = 'inbox';
        }

        try {
            $folder = $client->getFolder($folderName, null, true);
            if ($folder instanceof Folder) {
                return $folder;
            }
        } catch (\Throwable) {
        }

        try {
            $folders = $client->getFolders(false, null, true);
        } catch (\Throwable) {
            return null;
        }

        foreach ($folders as $candidate) {
            if (!$candidate instanceof Folder) {
                continue;
            }

            $name = strtolower(trim($candidate->name));
            $path = strtolower(trim($candidate->path));
            $fullName = strtolower(trim($candidate->full_name));

            if ($search === $name || $search === $path || $search === $fullName) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveExternalMessageId(Message $imapMessage): string
    {
        $header = $imapMessage->getHeader();
        $messageId = trim((string) ($header?->get('message_id') ?? ''));

        if ('' !== $messageId) {
            return $messageId;
        }

        return sprintf(
            'uid:%s:%s',
            strtolower(trim((string) $imapMessage->getFolderPath())),
            (string) $imapMessage->getUid()
        );
    }

    private function hydrateEntity(SupportEmailMessage $entity, Message $imapMessage, string $folderPath): void
    {
        $header = $imapMessage->getHeader();

        $fromAttribute = $header?->get('from');
        $from = $this->firstAddress($fromAttribute);

        $subject = trim((string) ($header?->get('subject') ?? ''));
        if ('' === $subject) {
            $subject = '(Ohne Betreff)';
        }

        $dateValue = $this->extractDate($header?->get('date'));
        $textBody = trim($imapMessage->getTextBody());
        $htmlBody = trim($imapMessage->getHTMLBody());
        if ('' === $textBody && '' !== $htmlBody) {
            $textBody = trim(strip_tags($htmlBody));
        }
        if ('' === $textBody) {
            $textBody = '(Kein Inhalt)';
        }

        $targetFolder = $this->mapFolder($folderPath);
        $sentAt = null;
        if ($targetFolder === SupportEmailMessage::FOLDER_SENT) {
            $sentAt = $dateValue ?? new \DateTimeImmutable();
        }

        $entity
            ->setFolder($targetFolder)
            ->setSubject($subject)
            ->setFromName($from['name'])
            ->setFromEmail($from['email'])
            ->setToEmail($this->formatAddressList($header?->get('to')))
            ->setCcEmail($this->formatAddressList($header?->get('cc')))
            ->setBccEmail($this->formatAddressList($header?->get('bcc')))
            ->setBodyText($textBody)
            ->setBodyHtml('' !== $htmlBody ? $htmlBody : null)
            ->setIsRead($imapMessage->hasFlag('seen'))
            ->setIsStarred($imapMessage->hasFlag('flagged'))
            ->setIsDraft($targetFolder === SupportEmailMessage::FOLDER_DRAFT || $imapMessage->hasFlag('draft'))
            ->setInReplyToMessageId($this->normalizeNullableString((string) ($header?->get('in_reply_to') ?? '')))
            ->setReceivedAt($dateValue)
            ->setSentAt($sentAt)
            ->setUpdatedAt(new \DateTimeImmutable());
    }

    /**
     * @return array{name:?string,email:?string}
     */
    private function firstAddress(?ImapAttribute $attribute): array
    {
        if (!$attribute instanceof ImapAttribute) {
            return ['name' => null, 'email' => null];
        }

        $first = $attribute->first();
        if ($first instanceof ImapAddress) {
            $name = trim($first->personal);
            $mail = trim($first->mail);

            return [
                'name' => '' !== $name ? $name : null,
                'email' => '' !== $mail ? $mail : null,
            ];
        }

        if (is_string($first) && false !== filter_var(trim($first), FILTER_VALIDATE_EMAIL)) {
            return ['name' => null, 'email' => trim($first)];
        }

        return ['name' => null, 'email' => null];
    }

    private function formatAddressList(?ImapAttribute $attribute): ?string
    {
        if (!$attribute instanceof ImapAttribute) {
            return null;
        }

        $mails = [];
        foreach ($attribute->all() as $entry) {
            if ($entry instanceof ImapAddress) {
                $mail = trim($entry->mail);
            } elseif (is_string($entry)) {
                $mail = trim($entry);
            } else {
                $mail = '';
            }

            if ('' === $mail) {
                continue;
            }

            if (preg_match('/<([^>]+)>/', $mail, $matches)) {
                $mail = trim((string) ($matches[1] ?? $mail));
            }

            if (false === filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $mails[strtolower($mail)] = $mail;
        }

        if ([] === $mails) {
            return null;
        }

        return implode(', ', array_values($mails));
    }

    private function extractDate(?ImapAttribute $attribute): ?\DateTimeImmutable
    {
        if (!$attribute instanceof ImapAttribute) {
            return null;
        }

        $value = $attribute->first();
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        $raw = trim((string) $attribute);
        if ('' === $raw) {
            return null;
        }

        try {
            return new \DateTimeImmutable($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapFolder(string $folderPath): string
    {
        $value = strtolower(trim($folderPath));

        if (str_contains($value, 'draft') || str_contains($value, 'entwurf')) {
            return SupportEmailMessage::FOLDER_DRAFT;
        }
        if (str_contains($value, 'sent') || str_contains($value, 'gesendet')) {
            return SupportEmailMessage::FOLDER_SENT;
        }
        if (str_contains($value, 'spam') || str_contains($value, 'junk')) {
            return SupportEmailMessage::FOLDER_SPAM;
        }
        if (str_contains($value, 'trash') || str_contains($value, 'deleted') || str_contains($value, 'papierkorb')) {
            return SupportEmailMessage::FOLDER_TRASH;
        }
        if (str_contains($value, 'archive') || str_contains($value, 'archiv')) {
            return SupportEmailMessage::FOLDER_ARCHIVE;
        }

        return SupportEmailMessage::FOLDER_INBOX;
    }

    private function normalizeImapEncryption(string $encryption): string|bool
    {
        $value = strtolower(trim($encryption));

        return match ($value) {
            EmailKonto::ENCRYPTION_SSL => 'ssl',
            EmailKonto::ENCRYPTION_TLS, EmailKonto::ENCRYPTION_STARTTLS => 'tls',
            default => false,
        };
    }

    /**
     * @return array{host:string,port:int,encryption:string|bool}
     */
    private function resolveImapConnectionConfig(EmailKonto $account): array
    {
        $rawHost = trim($account->getImapHost());
        $port = max(1, $account->getImapPort());
        $encryption = $this->normalizeImapEncryption($account->getImapEncryption());

        $host = $rawHost;
        $scheme = null;

        if (preg_match('~^(?P<scheme>[a-z][a-z0-9+.-]*)://~i', $rawHost, $matches)) {
            $parsed = parse_url($rawHost);
            if (is_array($parsed)) {
                $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
                $parsedHost = trim((string) ($parsed['host'] ?? ''));
                if ('' !== $parsedHost) {
                    $host = $parsedHost;
                }
                if (isset($parsed['port']) && is_int($parsed['port']) && $parsed['port'] > 0) {
                    $port = $parsed['port'];
                }
            }
        }

        if (str_contains($host, '/')) {
            $host = trim((string) strtok($host, '/'));
        }

        if (preg_match('/^([^:\/\s]+):(\d+)$/', $host, $matches)) {
            $host = trim((string) ($matches[1] ?? $host));
            $parsedPort = (int) ($matches[2] ?? 0);
            if ($parsedPort > 0) {
                $port = $parsedPort;
            }
        }

        if (str_starts_with($host, '[') && preg_match('/^\[([0-9a-fA-F:]+)\](?::(\d+))?$/', $host, $matches)) {
            $host = trim((string) ($matches[1] ?? $host));
            $parsedPort = (int) ($matches[2] ?? 0);
            if ($parsedPort > 0) {
                $port = $parsedPort;
            }
        }

        if (in_array($scheme, ['ssl', 'imaps'], true)) {
            $encryption = 'ssl';
        } elseif (in_array($scheme, ['tls', 'starttls'], true)) {
            $encryption = 'tls';
        }

        if (993 === $port && 'tls' === $encryption) {
            // Port 993 nutzt implizites TLS/SSL; STARTTLS auf 993 ist meist falsch.
            $encryption = 'ssl';
        }

        return [
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,
        ];
    }

    private function normalizeNullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return '' !== $trimmed ? $trimmed : null;
    }

    private function buildAccountLabel(EmailKonto $account): string
    {
        $name = trim($account->getName());
        if ('' !== $name) {
            return $name;
        }

        $email = trim($account->getEmailAddress());
        if ('' !== $email) {
            return $email;
        }

        return sprintf('Konto #%d', (int) ($account->getId() ?? 0));
    }

    private function truncateMessage(string $message): string
    {
        $value = trim($message);
        if ('' === $value) {
            return 'Unbekannter Fehler beim Abruf.';
        }

        if (strlen($value) > 500) {
            return substr($value, 0, 500) . '...';
        }

        return $value;
    }

    private function flattenThrowableMessage(\Throwable $throwable): string
    {
        $messages = [];
        $cursor = $throwable;
        $depth = 0;

        while ($cursor instanceof \Throwable && $depth < 6) {
            $message = trim($cursor->getMessage());
            if ('' !== $message && !in_array($message, $messages, true)) {
                $messages[] = $message;
            }

            $cursor = $cursor->getPrevious();
            ++$depth;
        }

        if ([] === $messages) {
            return 'Unbekannter Fehler';
        }

        return implode(' -> ', $messages);
    }
}
