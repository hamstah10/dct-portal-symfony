<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\MediaAsset;
use App\Entity\ShopOrder;
use App\Entity\ShopProduct;
use App\Repository\ShopProductRepository;
use App\Repository\ShopDownloadRepository;
use App\Repository\ShopProductDeliveryScopeRepository;
use App\Repository\ShopProductLinkRepository;
use App\Repository\ShopProductOptionRepository;
use App\Repository\ShopOrderRepository;
use App\Repository\ShopVideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AdminCrud(routePath: '/ecommerce/produkte', routeName: 'admin_shop_product')]
final class ShopProductCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly ShopProductRepository $repository,
        private readonly ShopOrderRepository $orderRepository,
        private readonly ShopProductOptionRepository $productOptionRepository,
        private readonly ShopProductDeliveryScopeRepository $productDeliveryScopeRepository,
        private readonly ShopProductLinkRepository $productLinkRepository,
        private readonly ShopVideoRepository $productVideoRepository,
        private readonly ShopDownloadRepository $productDownloadRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ShopProduct::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produkt')
            ->setEntityLabelInPlural('Produkte')
            ->setDefaultSort(['sorting' => 'ASC'])
            ->overrideTemplate('crud/index', 'admin/shop_product/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/shop_product/detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action): Action => $action
                ->setLabel('Details')
                ->asDefaultAction()
            );
    }

    public function configureFields(string $pageName): iterable
    {
        $moneyFormatter = static function (mixed $value): string {
            if (null === $value) {
                return '-';
            }

            $raw = trim((string) $value);
            if ('' === $raw) {
                return '-';
            }

            $normalized = str_replace(['€', ' '], '', $raw);
            $normalized = str_replace(',', '.', $normalized);
            if (!is_numeric($normalized)) {
                return $raw;
            }

            return number_format((float) $normalized, 2, ',', '.') . ' €';
        };

        $priceField = MoneyField::new('price', 'Preis')->setCurrency('EUR')->setStoredAsCents(false)->onlyOnForms();
        $specialPriceField = MoneyField::new('specialPrice', 'Sonderpreis')->setCurrency('EUR')->setStoredAsCents(false)->onlyOnForms();
        $priceViewField = TextField::new('price', 'Preis')->formatValue($moneyFormatter)->hideOnForm();
        $specialPriceViewField = TextField::new('specialPrice', 'Sonderpreis')->formatValue($moneyFormatter)->hideOnForm();
        $operatingCostsField = TextareaField::new('operatingCosts', 'Betriebskosten')
            ->hideOnIndex();

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('uid')->hideOnForm()->hideOnIndex(),
                TextField::new('title', 'Titel'),
                TextField::new('sku', 'SKU')->hideOnIndex(),
                $priceViewField,
                IntegerField::new('stock', 'Bestand'),
                BooleanField::new('isActive', 'Aktiv'),
                AssociationField::new('tags', 'Tags')->autocomplete()->setFormTypeOption('by_reference', false),
            ];
        }

        return [
            IdField::new('uid')->hideOnForm()->hideOnIndex(),
            FormField::addFieldset('Produkt'),
            TextField::new('title', 'Titel'),
            TextField::new('sku', 'SKU')->hideOnIndex(),
            TextField::new('slug', 'Slug')->hideOnIndex(),
            TextField::new('headline', 'Headline')->hideOnIndex(),
            TextField::new('badge', 'Badge')->hideOnIndex(),
            AssociationField::new('manufacturer', 'Hersteller')->autocomplete(),
            AssociationField::new('tax', 'Steuer')->autocomplete()->hideOnIndex(),
            $priceViewField,
            $specialPriceViewField->hideOnIndex(),
            $priceField,
            $specialPriceField->hideOnIndex(),
            $operatingCostsField,
            IntegerField::new('stock', 'Bestand'),
            NumberField::new('weight', 'Gewicht')->setNumDecimals(3)->hideOnIndex(),
            BooleanField::new('isActive', 'Aktiv'),
            BooleanField::new('shippingFree', 'Versandfrei')->hideOnIndex(),
            TextField::new('productType', 'Typ')->hideOnIndex(),
            FormField::addFieldset('Medien'),
            Field::new('uploadedImages', 'Bilder hochladen')
                ->onlyOnForms()
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'multiple' => true,
                    'attr' => ['accept' => 'image/*'],
                ])
                ->setHelp('Mehrere Bilder moeglich. Die Dateien werden als Medien gespeichert und direkt mit dem Produkt verknuepft.'),
            AssociationField::new('mediaAssets', 'Produktbilder')
                ->autocomplete()
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
            FormField::addFieldset('Beziehungen'),
            AssociationField::new('categories', 'Kategorien')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex(),
            AssociationField::new('tags', 'Tags')->autocomplete()->setFormTypeOption('by_reference', false),
            AssociationField::new('relatedProducts', 'Verwandte Produkte')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex(),
            AssociationField::new('shippingMethods', 'Versandarten')->autocomplete()->setFormTypeOption('by_reference', false)->hideOnIndex(),
            FormField::addFieldset('Inhalte'),
            CkEditor5Field::new('description', 'Beschreibung')->hideOnIndex(),
            TextareaField::new('shortDescription', 'Kurzbeschreibung')->hideOnIndex(),
            TextField::new('startupHelpHeadline', 'Startup Headline')->hideOnIndex(),
            TextareaField::new('startupHelpText', 'Startup Text')->hideOnIndex(),
            TextField::new('featuresHeadline', 'Features Headline')->hideOnIndex(),
            TextareaField::new('featuresText', 'Features Text')->hideOnIndex(),
            TextField::new('recommendationHeadline', 'Empfehlung Headline')->hideOnIndex(),
            TextareaField::new('recommendationText', 'Empfehlung Text')->hideOnIndex(),
            FormField::addFieldset('SEO & Metadaten'),
            TextField::new('metaTitle', 'Meta Title')->hideOnIndex(),
            TextareaField::new('metaDescription', 'Meta Description')->hideOnIndex(),
            TextareaField::new('metaKeywords', 'Meta Keywords')->hideOnIndex(),
            TextField::new('canonicalUrl', 'Canonical URL')->hideOnIndex(),
            TextField::new('lexofficeProductId', 'Lexoffice ID')->hideOnIndex(),
            TextareaField::new('extensions', 'Extensions')->hideOnIndex(),
            IntegerField::new('sorting', 'Sortierung')->hideOnIndex(),
            FormField::addFieldset('Auswertung'),
            IntegerField::new('deliveryScopeCount', 'Lieferumfang (Anzahl)')->onlyOnDetail(),
            IntegerField::new('categoriesCount', 'Kategorien (Anzahl)')->onlyOnDetail(),
            IntegerField::new('tagsCount', 'Tags (Anzahl)')->onlyOnDetail(),
            IntegerField::new('relatedProductsCount', 'Related (Anzahl)')->onlyOnDetail(),
            IntegerField::new('shippingMethodsCount', 'Versand (Anzahl)')->onlyOnDetail(),
            IntegerField::new('optionsCount', 'Optionen (Anzahl)')->onlyOnDetail(),
            IntegerField::new('imagesCount', 'Bilder (Anzahl)')->onlyOnDetail(),
            IntegerField::new('videosCount', 'Videos (Anzahl)')->onlyOnDetail(),
            IntegerField::new('documentsCount', 'Dokumente (Anzahl)')->onlyOnDetail(),
            IntegerField::new('linksCount', 'Links (Anzahl)')->onlyOnDetail(),
            FormField::addFieldset('Technik')->onlyOnDetail(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
            IntegerField::new('starttime')->onlyOnDetail(),
            IntegerField::new('endtime')->onlyOnDetail(),
            IntegerField::new('sysLanguageUid', 'Sprache')->onlyOnDetail(),
            IntegerField::new('l10nParent', 'L10n Parent')->onlyOnDetail(),
            TextField::new('l10nState', 'L10n State')->onlyOnDetail(),
            TextareaField::new('l10nDiffsource', 'L10n Diffsource')->onlyOnDetail(),
        ];
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_DETAIL !== $pageName) {
            return $responseParameters;
        }

        $entityDto = $responseParameters->get('entity');
        $product = null;
        if (is_object($entityDto) && method_exists($entityDto, 'getInstance')) {
            $instance = $entityDto->getInstance();
            if ($instance instanceof ShopProduct) {
                $product = $instance;
            }
        }

        if (!$product instanceof ShopProduct) {
            return $responseParameters;
        }

        $responseParameters->set('productOptions', $this->productOptionRepository->findBy(
            ['product' => $product],
            ['sorting' => 'ASC', 'title' => 'ASC']
        ));
        $responseParameters->set('deliveryScopes', $this->productDeliveryScopeRepository->findBy(
            ['product' => $product],
            ['sorting' => 'ASC', 'title' => 'ASC']
        ));
        $responseParameters->set('productLinks', $this->productLinkRepository->findBy(
            ['product' => $product],
            ['sorting' => 'ASC', 'title' => 'ASC']
        ));
        $responseParameters->set('productVideos', $this->productVideoRepository->findBy(
            ['product' => $product],
            ['sorting' => 'ASC', 'title' => 'ASC']
        ));
        $responseParameters->set('productDownloads', $this->productDownloadRepository->findBy(
            ['product' => $product],
            ['sorting' => 'ASC', 'title' => 'ASC']
        ));

        $responseParameters->set('lexofficeOrders', $this->findLexofficeOrdersForProduct($product));
        $responseParameters->set('mediaAssets', $product->getMediaAssets());

        return $responseParameters;
    }

    /**
     * @return array<int, array{
     *   id:int,
     *   orderNumber:string,
     *   createdAt:?int,
     *   invoiceId:?string,
     *   invoiceUrl:?string
     * }>
     */
    private function findLexofficeOrdersForProduct(ShopProduct $product): array
    {
        $productId = $product->getUid();
        if ($productId <= 0) {
            return [];
        }

        $needle = '%"productId":' . $productId . '%';
        $orders = $this->orderRepository->createQueryBuilder('o')
            ->andWhere('o.itemsJson LIKE :needle')
            ->andWhere('COALESCE(o.lexofficeInvoicePdf, \'\') <> \'\'')
            ->setParameter('needle', $needle)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($orders as $order) {
            if (!$order instanceof ShopOrder) {
                continue;
            }

            $itemsJson = $order->getItemsJson();
            if (null === $itemsJson || '' === trim($itemsJson)) {
                continue;
            }

            $items = json_decode($itemsJson, true);
            if (!is_array($items)) {
                continue;
            }

            $matchesProduct = false;
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $itemProductId = $item['productId'] ?? $item['product_id'] ?? $item['productUid'] ?? null;
                if (null !== $itemProductId && (int) $itemProductId === $productId) {
                    $matchesProduct = true;
                    break;
                }
            }

            if (!$matchesProduct) {
                continue;
            }

            $results[] = [
                'id' => $order->getUid(),
                'orderNumber' => (string) ($order->getOrderNumber() ?? ('#' . $order->getUid())),
                'createdAt' => $order->getCreatedAt() ?: null,
                'invoiceId' => $order->getLexofficeInvoiceId(),
                'invoiceUrl' => $order->getLexofficeInvoicePdf(),
            ];
        }

        return $results;
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProduct) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
            $this->handleProductImageUploads($entityInstance, $entityManager);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProduct) {
            $this->handleProductImageUploads($entityInstance, $entityManager);
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleProductImageUploads(ShopProduct $product, EntityManagerInterface $entityManager): void
    {
        $uploads = $product->getUploadedImages();
        if ([] === $uploads) {
            $product->setImagesCount($product->getMediaAssets()->count());

            return;
        }

        foreach ($uploads as $upload) {
            if (!$upload instanceof UploadedFile || !$upload->isValid()) {
                continue;
            }

            $mimeType = $upload->getClientMimeType() ?: $upload->getMimeType();
            if (!str_starts_with((string) $mimeType, 'image/')) {
                continue;
            }

            $assetName = $this->buildUploadedImageName($product, $upload);
            $fileSize = $upload->getSize();
            $path = $this->storeSingleUpload($upload, 'uploads/media/shop-products');
            if (null === $path) {
                continue;
            }

            $asset = new MediaAsset();
            $asset->setName($assetName);
            $asset->setFilePath($path);
            $asset->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null);
            $asset->setFileSize(is_int($fileSize) ? $fileSize : null);
            $asset->setMediaType(MediaAsset::TYPE_IMAGE);

            $entityManager->persist($asset);
            $product->addMediaAsset($asset);
        }

        $product->setImagesCount($product->getMediaAssets()->count());
        $product->setUploadedImages([]);
    }

    private function buildUploadedImageName(ShopProduct $product, UploadedFile $upload): string
    {
        $productTitle = trim($product->getTitle());
        $originalName = trim(pathinfo($upload->getClientOriginalName(), \PATHINFO_FILENAME));

        if ('' !== $productTitle && '' !== $originalName) {
            return $productTitle . ' - ' . $originalName;
        }

        if ('' !== $productTitle) {
            return $productTitle . ' Produktbild';
        }

        return '' !== $originalName ? $originalName : 'Produktbild';
    }
}
