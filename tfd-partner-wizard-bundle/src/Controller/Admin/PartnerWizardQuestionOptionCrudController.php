<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;

final class PartnerWizardQuestionOptionCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return PartnerWizardQuestionOption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Antwortoption')
            ->setEntityLabelInPlural('Antwortoptionen')
            ->setDefaultSort(['question' => 'ASC', 'priority' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('question', 'Frage');
        yield TextField::new('value', 'Wert')->setHelp('Interner Wert, z.B. "stage1".');
        yield TextField::new('title', 'Titel');
        yield TextareaField::new('text', 'Beschreibung')->hideOnIndex();
        yield AssociationField::new('mediaAsset', 'Icon (Media Manager)')
            ->renderAsNativeWidget()
            ->hideOnIndex()
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Icon auswaehlen')
            ->setFormTypeOption('attr', [
                'data-stage-icon-media-select' => '1',
                'data-placeholder' => 'Icon auswaehlen',
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
            });
        yield TextField::new('icon', 'Icon-URL (manuell)')->hideOnIndex()->setHelp('Alternative zum Media Manager oder Datei-Upload: direkte Bild-URL.');
        yield Field::new('uploadedIcon', 'Icon hochladen')
            ->onlyOnForms()
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required' => false,
                'attr' => ['accept' => 'image/*'],
            ]);
        yield TextField::new('mediaAsset', 'Icon Vorschau')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, PartnerWizardQuestionOption $option): string => $this->renderSingleImagePreview($option->getMediaAsset()?->getFilePath() ?? $option->getIcon()));
        yield IntegerField::new('numericValue', 'Zahlen-Wert (nur bei Volumen-Fragen)')->hideOnIndex();
        yield IntegerField::new('priority', 'Priorität');
        yield BooleanField::new('active', 'Aktiv');
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof PartnerWizardQuestionOption) {
            $this->handleIconUpload($entityInstance, $entityManager);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof PartnerWizardQuestionOption) {
            $this->handleIconUpload($entityInstance, $entityManager);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleIconUpload(PartnerWizardQuestionOption $option, EntityManagerInterface $entityManager): void
    {
        $upload = $option->getUploadedIcon();
        if (!$upload instanceof UploadedFile || !$upload->isValid()) {
            return;
        }

        $mimeType = $upload->getClientMimeType() ?: $upload->getMimeType();
        if (!str_starts_with((string) $mimeType, 'image/')) {
            return;
        }

        $path = $this->storeSingleUpload($upload, 'uploads/media/partner-wizard-options');
        if (null === $path) {
            return;
        }

        $asset = new MediaAsset();
        $asset->setName(trim($option->getTitle()) !== '' ? $option->getTitle() . ' Icon' : 'Options-Icon');
        $asset->setFilePath($path);
        $asset->setMimeType(trim((string) $mimeType) !== '' ? trim($mimeType) : null);
        $asset->setFileSize(is_int($upload->getSize()) ? $upload->getSize() : null);
        $asset->setMediaType(MediaAsset::TYPE_IMAGE);

        $entityManager->persist($asset);
        $option->setMediaAsset($asset);
        $option->setUploadedIcon(null);
    }
}
