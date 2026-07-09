<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AdminCrud(routePath: '/benutzer', routeName: 'admin_benutzer')]
final class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Benutzer')
            ->setEntityLabelInPlural('Benutzer')
            ->overrideTemplate('crud/detail', 'admin/user/detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $password = TextField::new('plainPassword', 'Passwort')
            ->onlyOnForms()
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp('Bei Bearbeiten leer lassen, wenn das Passwort gleich bleiben soll.')
            ->setFormTypeOption('empty_data', '');

        $activeField = BooleanField::new('isActive', 'Aktiv')->renderAsSwitch(true);
        $rolesField = ChoiceField::new('roles', 'Rollen')
            ->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'Benutzer' => 'ROLE_USER',
            ])
            ->allowMultipleChoices();
        $loginSinceField = TextField::new('loginSince', 'Angemeldet seit')
            ->setVirtual(true)
            ->formatValue(fn (mixed $value, ?User $user) => $this->formatLoginSince($user?->getLastLoginAt()));

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),
                $activeField->onlyOnIndex(),
                TextField::new('vorname', 'Vorname')->setRequired(false),
                TextField::new('nachname', 'Nachname')->setRequired(false),
                EmailField::new('email', 'E-Mail'),
                AssociationField::new('gruppe', 'Gruppe')
                    ->setRequired(false)
                    ->autocomplete(),
                $loginSinceField->hideOnForm(),
            ];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                FormField::addFieldset('Profil'),
                IdField::new('id'),
                $activeField,
                TextField::new('vorname', 'Vorname')->setRequired(false),
                TextField::new('nachname', 'Nachname')->setRequired(false),
                EmailField::new('email', 'E-Mail'),
                AssociationField::new('gruppe', 'Gruppe')
                    ->setRequired(false)
                    ->autocomplete(),
                $rolesField,
                FormField::addFieldset('Kontakt & Medien'),
                TextareaField::new('adresse', 'Adresse')
                    ->setRequired(false)
                    ->setFormTypeOption('attr', ['rows' => 3]),
                ImageField::new('bild', 'Bild')->setBasePath('/uploads/benutzer'),
                ImageField::new('avatar', 'Avatar')->setBasePath('/uploads/benutzer'),
                FormField::addFieldset('Aktivitaet'),
                DateTimeField::new('lastLoginAt', 'Letzter Login'),
                $loginSinceField,
            ];
        }

        return [
            FormField::addFieldset('Profil'),
            $activeField->hideOnIndex(),
            TextField::new('vorname', 'Vorname')->setRequired(false),
            TextField::new('nachname', 'Nachname')->setRequired(false),
            EmailField::new('email', 'E-Mail'),
            AssociationField::new('gruppe', 'Gruppe')
                ->setRequired(false)
                ->autocomplete(),
            $rolesField,
            FormField::addFieldset('Kontakt & Medien'),
            TextareaField::new('adresse', 'Adresse')
                ->setRequired(false)
                ->setFormTypeOption('attr', ['rows' => 3]),
            ImageField::new('bild', 'Bild')
                ->setBasePath('/uploads/benutzer')
                ->setUploadDir('public/uploads/benutzer')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[slug]-[contenthash].[extension]')
                ->setRequired(false),
            ImageField::new('avatar', 'Avatar')
                ->setBasePath('/uploads/benutzer')
                ->setUploadDir('public/uploads/benutzer')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[slug]-[contenthash].[extension]')
                ->setRequired(false),
            FormField::addFieldset('Sicherheit'),
            DateTimeField::new('lastLoginAt', 'Letzter Login')
                ->hideOnForm(),
            $loginSinceField->hideOnForm(),
            $password,
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        $plainPassword = $entityInstance->getPlainPassword();
        if (null === $plainPassword || '' === trim($plainPassword)) {
            return;
        }

        $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $plainPassword));
        $entityInstance->eraseCredentials();
    }

    private function formatLoginSince(?\DateTimeImmutable $loginAt): string
    {
        if (null === $loginAt) {
            return '-';
        }

        $now = new \DateTimeImmutable();
        $diff = $loginAt->diff($now);

        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d . 'd';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . 'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . 'm';
        }
        if ([] === $parts && $diff->s > 0) {
            $parts[] = $diff->s . 's';
        }

        $duration = [] === $parts ? 'gerade eben' : 'vor ' . implode(' ', $parts);

        return sprintf('%s (%s)', $loginAt->format('d.m.Y H:i'), $duration);
    }
}
