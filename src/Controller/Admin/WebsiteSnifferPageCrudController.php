<?php

namespace App\Controller\Admin;

use App\Entity\WebsiteSnifferPage;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/websites/sniffer/pages', routeName: 'admin_websites_sniffer_page')]
final class WebsiteSnifferPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WebsiteSnifferPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sniffer-Seite')
            ->setEntityLabelInPlural('Sniffer-Seiten')
            ->setDefaultSort(['createdAt' => 'DESC', 'id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('run', 'Lauf'),
            TextField::new('relativePath', 'Datei (relativ)'),
            TextField::new('title', 'HTML Title')
                ->setRequired(false),
            TextField::new('screenshotPath', 'Screenshot')
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderScreenshotPreview($value)),
            TextField::new('filePath', 'Pfad / URL')
                ->hideOnIndex(),
            TextField::new('h1', 'Erste H1')
                ->setRequired(false)
                ->hideOnIndex(),
            IntegerField::new('bodyTextLength', 'Body Zeichen')
                ->setRequired(false),
            IntegerField::new('fileSize', 'Dateigroesse (Bytes)')
                ->setRequired(false),
            TextareaField::new('metaDescription', 'Meta Description')
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('relativePath', 'Parser-Daten')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?WebsiteSnifferPage $entity): string => $this->renderParsedData($entity?->getParsedData())),
            TextareaField::new('htmlContent', 'HTML Quelle')
                ->setRequired(false)
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderHtmlSource($value)),
            DateTimeField::new('createdAt', 'Erfasst am')
                ->hideOnForm(),
        ];
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $runId = $this->getContext()?->getRequest()->query->get('runId');
        if (is_scalar($runId) && ctype_digit((string) $runId)) {
            $alias = $queryBuilder->getRootAliases()[0] ?? 'entity';
            $queryBuilder
                ->andWhere(sprintf('IDENTITY(%s.run) = :runId', $alias))
                ->setParameter('runId', (int) $runId);
        }

        return $queryBuilder;
    }

    private function renderScreenshotPreview(?string $value): string
    {
        $path = trim((string) $value);
        if ('' === $path) {
            return '<span class="text-muted">Kein Screenshot</span>';
        }

        $url = '/' . ltrim($path, '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<button type="button" data-lightbox-url="%1$s" data-lightbox-caption="Sniffer Screenshot" style="border:0;background:transparent;padding:0;margin:0;cursor:zoom-in;"><img src="%1$s" alt="Screenshot" style="width:180px;max-width:100%%;height:auto;object-fit:cover;border-radius:6px;border:1px solid #d5d9e0;"></button>',
            $escapedUrl
        );
    }

    private function renderParsedData(mixed $value): string
    {
        if (!is_array($value) || [] === $value) {
            return '<span class="text-muted">Keine Parser-Daten</span>';
        }

        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (false === $json || '' === $json) {
            return '<span class="text-muted">Parser-Daten konnten nicht angezeigt werden</span>';
        }

        $escaped = htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<pre style="white-space:pre-wrap;max-height:420px;overflow:auto;margin:0;background:#0f1621;color:#dce4ef;padding:12px;border-radius:6px;border:1px solid #29384d;">%s</pre>',
            $escaped
        );
    }

    private function renderHtmlSource(?string $value): string
    {
        $html = trim((string) $value);
        if ('' === $html) {
            return '<span class="text-muted">Keine HTML-Quelle</span>';
        }

        $escaped = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<pre style="white-space:pre-wrap;max-height:520px;overflow:auto;margin:0;background:#0f1621;color:#dce4ef;padding:12px;border-radius:6px;border:1px solid #29384d;">%s</pre>',
            $escaped
        );
    }
}
