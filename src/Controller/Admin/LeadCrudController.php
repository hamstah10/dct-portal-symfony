<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Lead;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/leads', routeName: 'admin_leads')]
final class LeadCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Lead::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Lead')
            ->setEntityLabelInPlural('Leads')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateShowcasePasswordAction = Action::new('generateShowcasePassword', 'Showcase-Passwort')
            ->setIcon('mdi:key-plus')
            ->linkToRoute('admin_leads_generate_showcase_password', static function (Lead $lead): array {
                return ['id' => $lead->getId()];
            })
            ->displayIf(static fn (Lead $lead): bool => null !== $lead->getId());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $generateShowcasePasswordAction)
            ->add(Crud::PAGE_DETAIL, $generateShowcasePasswordAction)
            ->add(Crud::PAGE_EDIT, $generateShowcasePasswordAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $showcaseAccessField = TextField::new('showcasePasswordHash', 'Showcase-Zugang')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(static function (mixed $value, ?Lead $lead): string {
                if (!$lead instanceof Lead || !$lead->hasShowcasePassword()) {
                    return '<span class="badge badge-secondary">Nicht eingerichtet</span>';
                }

                if ($lead->hasActiveShowcaseAccess()) {
                    return '<span class="badge badge-success">Aktiv</span>';
                }

                return '<span class="badge badge-danger">Abgelaufen</span>';
            });

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('firma', 'Firma'),
            TextField::new('vorname', 'Vorname'),
            TextField::new('nachname', 'Nachname'),
            EmailField::new('email', 'E-Mail'),
            TextField::new('telefon', 'Telefon'),
            TextField::new('quelle', 'Quelle'),
            ChoiceField::new('status', 'Status')
                ->setChoices(Lead::getStatusChoices())
                ->renderAsBadges([
                    Lead::STATUS_NEW => 'secondary',
                    Lead::STATUS_CONTACTED => 'info',
                    Lead::STATUS_QUALIFIED => 'warning',
                    Lead::STATUS_OFFER => 'primary',
                    Lead::STATUS_WON => 'success',
                    Lead::STATUS_LOST => 'danger',
                ]),
            BooleanField::new('newsletter', 'Newsletter')
                ->renderAsSwitch(false),
            $showcaseAccessField,
            DateTimeField::new('showcasePasswordExpiresAt', 'Showcase gueltig bis')->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Optionales Ablaufdatum fuer den Showcase-Login. Ohne Datum bleibt der Zugang aktiv, bis ein neues Passwort generiert wird.'),
            DateTimeField::new('showcasePasswordGeneratedAt', 'Showcase generiert am')->hideOnIndex(),
                
            TextareaField::new('notiz', 'Notiz')
                ->setRequired(false)
                ->hideOnIndex(),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm()->hideOnIndex(),
        ];
    }

    #[Route('/admin/leads/{id}/generate-showcase-password', name: 'admin_leads_generate_showcase_password', methods: ['GET'])]
    public function generateShowcasePassword(Lead $lead, AdminContext $context): RedirectResponse
    {
        $plainPassword = $this->generateReadablePassword();
        $expiresAt = $lead->getShowcasePasswordExpiresAt() ?? (new \DateTimeImmutable('+7 days'))->setTime(23, 59, 59);

        $lead->setGeneratedShowcasePassword($plainPassword, $expiresAt);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            sprintf(
                'Showcase-Passwort fuer %s: %s (gueltig bis %s)',
                (string) $lead,
                $plainPassword,
                $expiresAt->format('d.m.Y H:i')
            )
        );

        $referrer = $context->getRequest()->headers->get('referer');
        if (is_string($referrer) && '' !== $referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirect($this->buildDetailUrl($lead));
    }

    private function buildDetailUrl(Lead $lead): string
    {
        return $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $lead->getId())
            ->generateUrl();
    }

    private function generateReadablePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $alphabetLength = strlen($alphabet);
        $password = '';

        for ($i = 0; $i < 12; ++$i) {
            $password .= $alphabet[random_int(0, $alphabetLength - 1)];
        }

        return $password;
    }
}
