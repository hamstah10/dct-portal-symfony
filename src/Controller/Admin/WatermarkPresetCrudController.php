<?php

namespace App\Controller\Admin;

use App\Entity\WatermarkPreset;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/einstellungen/wasserzeichen/vorlagen', routeName: 'admin_watermark_preset')]
final class WatermarkPresetCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public static function getEntityFqcn(): string
    {
        return WatermarkPreset::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Wasserzeichen-Vorlage')
            ->setEntityLabelInPlural('Wasserzeichen-Vorlagen')
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield TextField::new('name', 'Name');
        yield Field::new('imageUpload', 'Bild hochladen')
            ->onlyOnForms()
            ->setFormType(DropzoneType::class)
            ->setFormTypeOptions([
                'mapped'   => false,
                'required' => false,
                'attr'     => ['accept' => 'image/*'],
            ]);
        yield TextField::new('imagePath', 'Bild-Pfad')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(static function (?string $value): string {
                if (null === $value || '' === $value) {
                    return '<span class="text-muted">–</span>';
                }
                $url = str_starts_with($value, '/') ? $value : '/' . $value;
                $esc = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                return sprintf('<img src="%s" style="height:36px;object-fit:contain;">', $esc);
            });
        yield TextField::new('text', 'Text')->hideOnIndex();
        yield ChoiceField::new('position', 'Position')
            ->setChoices(WatermarkPreset::POSITIONS);
        yield NumberField::new('opacity', 'Deckkraft (0.0–1.0)')
            ->setNumDecimals(2)
            ->setHelp('z.B. 0.5 = 50%');
        yield IntegerField::new('fontSize', 'Schriftgröße (px)')->hideOnIndex();
        yield TextField::new('textColor', 'Textfarbe (Hex)')->hideOnIndex();
        yield IntegerField::new('imageWidth', 'Bildbreite (px)')->hideOnIndex()->setHelp('Leer lassen für automatische Größe');
        yield BooleanField::new('fillWidth', 'Bildbreite ausfüllen')->hideOnIndex()->renderAsSwitch(false)->setHelp('Wasserzeichen auf volle Bildbreite skalieren (überschreibt Bildbreite)');
        yield NumberField::new('marginPercent', 'Randabstand (%)')
            ->hideOnIndex()
            ->setNumDecimals(2)
            ->setHelp('Abstand zum Bildrand in Prozent der Bildbreite/-höhe, z. B. 3 = 3%. Gilt auch, wenn "Bildbreite ausfüllen" aktiv ist.');
        yield BooleanField::new('active', 'Aktiv');
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->handleImageUpload($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->handleImageUpload($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleImageUpload(object $entity): void
    {
        if (!$entity instanceof WatermarkPreset) {
            return;
        }

        $request = $this->getContext()?->getRequest();
        $uploaded = $request?->files->get('WatermarkPreset')['imageUpload'] ?? null;
        if (null === $uploaded) {
            return;
        }

        $path = $this->storeSingleUpload($uploaded, 'uploads/watermarks');
        if (null !== $path) {
            $entity->setImagePath($path);
        }
    }
}
