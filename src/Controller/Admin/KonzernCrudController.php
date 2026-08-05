<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Konzern;
use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/konzern', routeName: 'admin_konzern')]
final class KonzernCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return Konzern::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Konzern')
            ->setEntityLabelInPlural('Konzerne');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('resolvedLogoPath', 'Logo')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $logo = trim((string) ($value ?? ''));
                    if ('' === $logo) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (!str_starts_with($logo, '/') && !preg_match('#^https?://#i', $logo)) {
                        $logo = '/' . $logo;
                    }

                    $escapedLogo = htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Logo" style="width:42px;height:42px;object-fit:contain;border:1px solid #d9e0ea;border-radius:6px;background:#fff;padding:4px;">',
                        $escapedLogo
                    );
                }),
            TextField::new('name'),
            BooleanField::new('active', 'Aktiv'),

            FormField::addFieldset('Sitz der Gesellschaft'),
            TextField::new('strasse', 'Straße')->hideOnIndex()->setRequired(false),
            TextField::new('hausnummer', 'Hausnummer')->hideOnIndex()->setRequired(false),
            TextField::new('plz', 'PLZ')->hideOnIndex()->setRequired(false),
            TextField::new('stadt', 'Stadt')->hideOnIndex()->setRequired(false),
            TextField::new('land', 'Land')->hideOnIndex()->setRequired(false),

            FormField::addFieldset('Logo'),
            Field::new('uploadedLogo', 'Logo hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('logoMediaAsset', 'Logo (Media Manager)')
                ->renderAsNativeWidget()
                ->hideOnIndex()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Logo auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Logo auswaehlen',
                ])
                ->setFormTypeOption('choice_attr', static function (?MediaAsset $mediaAsset, string $key, mixed $value): array {
                    if (!$mediaAsset instanceof MediaAsset) {
                        return [];
                    }

                    $filePath = trim((string) $mediaAsset->getFilePath());
                    if ('' === $filePath) {
                        return [];
                    }

                    if (!str_starts_with($filePath, '/') && !preg_match('#^https?://#i', $filePath)) {
                        $filePath = '/' . $filePath;
                    }

                    return [
                        'data-image' => $filePath,
                        'data-name' => $mediaAsset->getName(),
                    ];
                })
                ->setQueryBuilder(static function (QueryBuilder $queryBuilder): QueryBuilder {
                    $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');

                    return $queryBuilder
                        ->resetDQLPart('orderBy')
                        ->andWhere(sprintf('%s.active = :active', $rootAlias))
                        ->andWhere(sprintf('%s.mediaType IN (:mediaTypes)', $rootAlias))
                        ->setParameter('active', true)
                        ->setParameter('mediaTypes', [MediaAsset::TYPE_ICON, MediaAsset::TYPE_IMAGE])
                        ->orderBy(sprintf('%s.name', $rootAlias), 'ASC');
                }),
            TextField::new('logo', 'Logo Pfad (Fallback)')
                ->hideOnIndex()
                ->setHelp('Optional: manueller Pfad. Bei Upload wird dieser Pfad automatisch gesetzt.'),
            TextField::new('resolvedLogoPath', 'Logo Vorschau')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),

            FormField::addFieldset('Marken'),
            AssociationField::new('brandMasters', 'Marken')
                ->setFormTypeOptions([
                    'multiple' => true,
                    'by_reference' => false,
                ])
                ->setHelp('Welche Marken zu diesem Konzern gehören (z.B. Audi, VW, Seat, Skoda, Porsche).'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Konzern) {
            $this->handleLogoUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Konzern) {
            $this->handleLogoUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleLogoUpload(Konzern $konzern): void
    {
        $path = $this->storeSingleUpload($konzern->getUploadedLogo(), 'uploads/konzern');
        if (null !== $path) {
            $konzern->setLogo($path);
            $konzern->setLogoMediaAsset(null);
        }

        $konzern->setUploadedLogo(null);
    }
}
