<?php

namespace App\Controller\Admin;

use App\Entity\WebhookEndpoint;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/webhooks', routeName: 'admin_webhooks')]
final class WebhookEndpointCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WebhookEndpoint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Webhook-Endpunkt')
            ->setEntityLabelInPlural('Webhook-Endpunkte')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('targetUrl', 'Ziel-URL'),
            ChoiceField::new('subscribedEvents', 'Events')
                ->setChoices(WebhookEndpoint::getAvailableEventsChoices())
                ->allowMultipleChoices()
                ->setRequired(true),
            TextField::new('secret', 'Signing Secret')
                ->setRequired(false)
                ->setHelp('Optional. Falls gesetzt, wird X-Webhook-Signature (HMAC SHA-256) gesendet.')
                ->hideOnIndex(),
            IntegerField::new('timeoutSeconds', 'Timeout (Sek.)'),
            BooleanField::new('verifySsl', 'SSL pruefen'),
            BooleanField::new('active', 'Aktiv'),
            DateTimeField::new('lastDeliveryAt', 'Letzte Zustellung')->hideOnForm(),
            IntegerField::new('lastHttpStatus', 'Letzter HTTP-Status')->hideOnForm(),
            TextareaField::new('lastError', 'Letzter Fehler')->hideOnForm(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }
}
