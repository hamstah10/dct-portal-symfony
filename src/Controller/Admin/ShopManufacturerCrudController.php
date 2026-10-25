<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\ShopManufacturer;
use App\Repository\ShopManufacturerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/hersteller', routeName: 'admin_shop_manufacturer')]
final class ShopManufacturerCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopManufacturerRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopManufacturer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Hersteller')
            ->setEntityLabelInPlural('Hersteller')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Name'),
            TextField::new('slug', 'Slug')->hideOnIndex(),
            AssociationField::new('logoMediaAsset', 'Logo')
                ->setRequired(false)
                ->setQueryBuilder(static fn (QueryBuilder $qb) => $qb->andWhere('entity.mediaType = :image')->setParameter('image', MediaAsset::TYPE_IMAGE)->orderBy('entity.name', 'ASC'))
                ->setFormTypeOption('placeholder', 'Logo auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Logo auswaehlen',
                ])
                ->setFormTypeOption('choice_attr', static function (?MediaAsset $mediaAsset): array {
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

                    return ['data-image' => $filePath, 'data-name' => $mediaAsset->getName()];
                })
                ->hideOnIndex(),
            TextField::new('website', 'Website')->hideOnIndex(),
            IntegerField::new('manufacturerPage', 'Seiten-ID')->hideOnIndex(),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            IntegerField::new('sorting', 'Sortierung'),
            BooleanField::new('hidden', 'Versteckt'),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('starttime', 'Start')->onlyOnDetail(),
            IntegerField::new('endtime', 'Ende')->onlyOnDetail(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
            IntegerField::new('sysLanguageUid', 'Sprache')->onlyOnDetail(),
            IntegerField::new('l10nParent', 'L10n Parent')->onlyOnDetail(),
            TextField::new('l10nState', 'L10n State')->onlyOnDetail(),
            TextareaField::new('l10nDiffsource', 'L10n Diffsource')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopManufacturer) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopManufacturer) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
