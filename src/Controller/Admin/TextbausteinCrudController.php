<?php

namespace App\Controller\Admin;

use App\Entity\Textbaustein;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/einstellungen/textbausteine', routeName: 'admin_textbaustein')]
final class TextbausteinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Textbaustein::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Textbaustein')
            ->setEntityLabelInPlural('Textbausteine')
            ->setDefaultSort(['sorting' => 'ASC', 'key' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('key', 'Schlüssel')
                ->setHelp('Eindeutiger Bezeichner, z.B. <code>welcome_message</code>')
                ->setColumns(6),

            ChoiceField::new('type', 'Typ')
                ->setChoices(Textbaustein::getTypeChoices())
                ->renderAsBadges([
                    Textbaustein::TYPE_SHORT => 'info',
                    Textbaustein::TYPE_LONG => 'warning',
                ])
                ->setColumns(3),

            BooleanField::new('active', 'Aktiv')
                ->setColumns(3),

            TextField::new('textDe', 'Text (Deutsch)')
                ->onlyOnIndex()
                ->formatValue(static function (?string $value): string {
                    if (null === $value || '' === $value) {
                        return '<span class="text-muted">–</span>';
                    }
                    $plain = strip_tags($value);

                    return mb_strlen($plain) > 80
                        ? htmlspecialchars(mb_substr($plain, 0, 80), ENT_QUOTES, 'UTF-8') . '…'
                        : htmlspecialchars($plain, ENT_QUOTES, 'UTF-8');
                })
                ->renderAsHtml(),

            TextareaField::new('textDe', 'Text Deutsch')
                ->onlyOnForms()
                ->setRequired(false)
                ->setNumOfRows(5)
                ->setHelp('Deutscher Text'),

            TextareaField::new('textEn', 'Text Englisch')
                ->onlyOnForms()
                ->setRequired(false)
                ->setNumOfRows(5)
                ->setHelp('English text'),

            TextField::new('description', 'Interne Notiz')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Nur für interne Dokumentation, wird nicht angezeigt'),

            IntegerField::new('sorting', 'Sortierung')
                ->hideOnIndex(),
        ];
    }
}
