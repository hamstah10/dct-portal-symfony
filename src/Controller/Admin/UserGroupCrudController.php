<?php

namespace App\Controller\Admin;

use App\Entity\UserGroup;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

#[AdminCrud(routePath: '/benutzer-gruppen', routeName: 'admin_user_group')]
final class UserGroupCrudController extends AbstractCrudController
{
    private const FIREWALL_NAME = 'main';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return UserGroup::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Benutzergruppe')
            ->setEntityLabelInPlural('Benutzergruppen')
            ->setDefaultSort(['sorting' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $presetChoices = [
            'Admin' => 'ROLE_ADMIN',
            'Benutzer' => 'ROLE_USER',
            'Portal TFD (User)' => 'ROLE_PORTAL_TFD_USER',
            'Portal TFD (Admin)' => 'ROLE_PORTAL_TFD_ADMIN',
            'Portal CPT (User)' => 'ROLE_PORTAL_CPT_USER',
            'Portal CPT (Admin)' => 'ROLE_PORTAL_CPT_ADMIN',
        ];

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            ArrayField::new('roles', 'Rollen')
                ->onlyOnIndex()
                ->formatValue(static function (mixed $value): string {
                    return is_array($value) ? implode(', ', $value) : '';
                }),
            ChoiceField::new('rolesPreset', 'Rollen (Preset)')
                ->setChoices($presetChoices)
                ->allowMultipleChoices()
                ->onlyOnForms(),
            TextField::new('rolesInput', 'Rollen')
                ->onlyOnForms()
                ->setHelp('Kommagetrennt, z.B. user, admin oder ROLE_PORTAL_TFD_ADMIN.'),
            IntegerField::new('sorting', 'Sortierung')
                ->setRequired(false),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $this->refreshCurrentUserTokenIfNeeded($entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        $this->refreshCurrentUserTokenIfNeeded($entityInstance);
    }

    private function refreshCurrentUserTokenIfNeeded(mixed $entityInstance): void
    {
        if (!$entityInstance instanceof UserGroup) {
            return;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return;
        }

        $group = $user->getGruppe();
        if (!$group instanceof UserGroup) {
            return;
        }

        $groupId = $group->getId();
        $entityId = $entityInstance->getId();
        if (null === $groupId || null === $entityId || $groupId !== $entityId) {
            return;
        }

        $freshUser = $this->userRepository->find($user->getId());
        if (!$freshUser instanceof User) {
            return;
        }

        $token = new PostAuthenticationToken($freshUser, self::FIREWALL_NAME, $freshUser->getRoles());
        $existingToken = $this->tokenStorage->getToken();
        if (null !== $existingToken) {
            $token->setAttributes($existingToken->getAttributes());
        }

        $this->tokenStorage->setToken($token);
    }
}
