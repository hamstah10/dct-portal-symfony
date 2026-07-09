<?php

namespace App\Controller\Admin;

use App\Entity\EmailKonto;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/verwaltung/email-konten', routeName: 'admin_email_konto')]
final class EmailKontoCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return EmailKonto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('E-Mail Konto')
            ->setEntityLabelInPlural('E-Mail Konten')
            ->setDefaultSort(['sorting' => 'ASC', 'name' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('firma', 'Firma')
                ->setRequired(false),
            TextField::new('name', 'Bezeichnung'),
            EmailField::new('emailAddress', 'E-Mail Adresse'),
            TextField::new('fromName', 'Absendername')
                ->setRequired(false)
                ->hideOnIndex(),

            FormField::addFieldset('IMAP (Abruf)'),
            TextField::new('imapHost', 'IMAP Host')
                ->setHelp('Nur Hostname eintragen, z. B. mail.tuningfux.de (ohne tls:// und ohne :Port).'),
            IntegerField::new('imapPort', 'IMAP Port'),
            ChoiceField::new('imapEncryption', 'IMAP Verschluesselung')
                ->setChoices(EmailKonto::getEncryptionChoices())
                ->setHelp('Empfehlung: Port 993 = SSL, Port 143 = TLS/STARTTLS.'),
            TextField::new('imapUsername', 'IMAP Benutzer')
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('imapPassword', 'IMAP Passwort')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Leer lassen, um das bestehende Passwort beizubehalten.')
                ->setFormTypeOption('attr', [
                    'type' => 'password',
                    'autocomplete' => 'new-password',
                ]),
            TextField::new('imapFolder', 'IMAP Ordner'),
            BooleanField::new('fetchEnabled', 'Abruf aktiv'),

            FormField::addFieldset('SMTP (Versand)'),
            TextField::new('smtpHost', 'SMTP Host')
                ->hideOnIndex(),
            IntegerField::new('smtpPort', 'SMTP Port')
                ->hideOnIndex(),
            ChoiceField::new('smtpEncryption', 'SMTP Verschluesselung')
                ->setChoices(EmailKonto::getEncryptionChoices())
                ->setHelp('Empfehlung: Port 465 = SSL, Port 587 = TLS/STARTTLS.')
                ->hideOnIndex(),
            TextField::new('smtpUsername', 'SMTP Benutzer')
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('smtpPassword', 'SMTP Passwort')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Leer lassen, um das bestehende Passwort beizubehalten.')
                ->setFormTypeOption('attr', [
                    'type' => 'password',
                    'autocomplete' => 'new-password',
                ]),
            BooleanField::new('sendEnabled', 'Versand aktiv')
                ->hideOnIndex(),
            AssociationField::new('labels', 'Labels')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->autocomplete()
                ->setRequired(false)
                ->onlyOnForms(),

            FormField::addFieldset('Allgemein'),
            BooleanField::new('active', 'Aktiv'),
            IntegerField::new('sorting', 'Sortierung')
                ->hideOnIndex(),
            TextareaField::new('notes', 'Notizen')
                ->setRequired(false)
                ->hideOnIndex()
                ->setFormTypeOption('attr', ['rows' => 4]),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')
                ->hideOnForm()
                ->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EmailKonto) {
            $this->normalizePasswordsOnCreate($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EmailKonto) {
            $this->preservePasswordsOnEmptyInput($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function normalizePasswordsOnCreate(EmailKonto $account): void
    {
        if ('' === trim((string) ($account->getImapPassword() ?? ''))) {
            $account->setImapPassword(null);
        }

        if ('' === trim((string) ($account->getSmtpPassword() ?? ''))) {
            $account->setSmtpPassword(null);
        }
    }

    private function preservePasswordsOnEmptyInput(EmailKonto $account): void
    {
        $id = $account->getId();
        if (null === $id) {
            $this->normalizePasswordsOnCreate($account);

            return;
        }

        $imapInput = trim((string) ($account->getImapPassword() ?? ''));
        $smtpInput = trim((string) ($account->getSmtpPassword() ?? ''));
        if ('' !== $imapInput && '' !== $smtpInput) {
            return;
        }

        $row = $this->connection->fetchAssociative(
            'SELECT imap_password, smtp_password FROM email_konto WHERE id = :id',
            ['id' => $id]
        );

        if (!is_array($row)) {
            return;
        }

        if ('' === $imapInput) {
            $stored = isset($row['imap_password']) ? trim((string) $row['imap_password']) : '';
            $account->setImapPassword('' !== $stored ? $stored : null);
        }

        if ('' === $smtpInput) {
            $stored = isset($row['smtp_password']) ? trim((string) $row['smtp_password']) : '';
            $account->setSmtpPassword('' !== $stored ? $stored : null);
        }
    }
}
