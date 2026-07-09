<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tuningfux\InstagramBundle\Entity\InstagramAccount;

class InstagramAccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InstagramAccount::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Instagram Account')
            ->setEntityLabelInPlural('Instagram Accounts');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('instagramUserId', 'Instagram User ID')
                ->hideOnForm()
                ->setDisabled(true),
            TextField::new('username', 'Username')
                ->hideOnForm()
                ->setDisabled(true),
            TextField::new('accessToken', 'Access Token')
                ->hideOnForm()
                ->setDisabled(true),
            DateTimeField::new('tokenExpiresAt', 'Token Expires At'),
            TextField::new('accountType', 'Account Type')
                ->setHelp('personal oder business'),
            TextField::new('profilePictureUrl', 'Profile Picture URL')
                ->hideOnForm(),
            DateTimeField::new('createdAt', 'Created At')->onlyOnIndex(),
            DateTimeField::new('updatedAt', 'Updated At')->onlyOnIndex(),
        ];
    }
}
