<?php

namespace App\Controller\Admin;

use App\Entity\EcuOption;
use App\Entity\EcuOptionMaster;
use App\Entity\MediaAsset;
use App\Repository\EcuOptionHintRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-option-master', routeName: 'admin_ecu_option_master')]
final class EcuOptionMasterCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EcuOptionHintRepository $ecuOptionHintRepository,
    ) {}

    public static function getEntityFqcn(): string
    {
        return EcuOptionMaster::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ECU-Option-Master')
            ->setEntityLabelInPlural('ECU-Option-Master')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action): Action => $action
                ->setLabel('Loeschen')
                ->setIcon('internal:delete')
                ->asDangerAction()
                ->askConfirmation())
            ->update(Crud::PAGE_DETAIL, Action::DELETE, static fn (Action $action): Action => $action
                ->setLabel('Loeschen')
                ->setIcon('internal:delete')
                ->asDangerAction()
                ->askConfirmation());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('resolvedIconPath', 'Icon')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $icon = trim((string) ($value ?? ''));
                    if ('' === $icon) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (str_contains($icon, ':') && !preg_match('#^https?://#i', $icon)) {
                        $escapedIcon = htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;gap:8px;"><iconify-icon icon="%s" width="26" height="26"></iconify-icon><code>%s</code></span>',
                            $escapedIcon,
                            $escapedIcon
                        );
                    }

                    if (!str_starts_with($icon, '/') && !preg_match('#^https?://#i', $icon)) {
                        $icon = '/' . $icon;
                    }

                    $escapedIcon = htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Icon" style="width:42px;height:42px;object-fit:contain;border:1px solid #d9e0ea;border-radius:6px;background:#fff;padding:4px;">',
                        $escapedIcon
                    );
                }),
            TextField::new('name'),
            TextField::new('slug'),
            AssociationField::new('iconMediaAsset', 'Icon (Media Manager)')
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
                }),
            TextField::new('icon', 'Icon Pfad (Fallback)')
                ->hideOnIndex()
                ->setFormTypeOptions(['attr' => ['id' => 'ecu_option_master_icon_input', 'autocomplete' => 'off']])
                ->setHelp($this->buildIconPreviewHelp()),
            TextField::new('moduleSlug', 'Modul-Slug')
                ->hideOnIndex()
                ->setFormTypeOptions(['attr' => ['list' => 'ecu-module-slug-list', 'autocomplete' => 'off']])
                ->setHelp($this->buildModuleSlugDatalist()),
            TextareaField::new('description')->hideOnIndex(),
        ];
    }

    private function buildIconPreviewHelp(): string
    {
        return <<<'HTML'
<span id="ecu-option-master-icon-preview" style="display:inline-flex;align-items:center;gap:12px;margin-top:6px;">
  <span id="ecu-option-master-icon-render" style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border:1px solid #d9e0ea;border-radius:6px;background:#fff;"></span>
  <span class="text-muted" id="ecu-option-master-icon-label" style="font-size:.85em;"></span>
</span>
<script>
(function () {
  function updateIconPreview(value) {
    var render = document.getElementById('ecu-option-master-icon-render');
    var label  = document.getElementById('ecu-option-master-icon-label');
    if (!render) return;
    var v = (value || '').trim();
    render.innerHTML = '';
    label.textContent = '';
    if (v === '') return;
    if (v.indexOf(':') !== -1 && !/^https?:\/\//i.test(v)) {
      var ic = document.createElement('iconify-icon');
      ic.setAttribute('icon', v);
      ic.setAttribute('width', '32');
      ic.setAttribute('height', '32');
      render.appendChild(ic);
      label.textContent = v;
    } else {
      var path = v;
      if (!/^\//.test(path) && !/^https?:\/\//i.test(path)) path = '/' + path;
      var img = document.createElement('img');
      img.src = path;
      img.alt = 'Icon';
      img.style.cssText = 'width:40px;height:40px;object-fit:contain;';
      render.appendChild(img);
      label.textContent = path;
    }
  }
  document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('ecu_option_master_icon_input');
    if (!input) return;
    updateIconPreview(input.value);
    input.addEventListener('input', function () { updateIconPreview(this.value); });
  });
})();
</script>
HTML;
    }

    private function buildModuleSlugDatalist(): string
    {
        $slugs = $this->ecuOptionHintRepository->findDistinctModuleSlugs();
        $options = implode('', array_map(
            static fn (string $slug): string => sprintf('<option value="%s">', htmlspecialchars($slug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            $slugs,
        ));

        return sprintf('<datalist id="ecu-module-slug-list">%s</datalist>', $options);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuOptionMaster) {
            $options = $entityManager->getRepository(EcuOption::class)->findBy([
                'masterOption' => $entityInstance,
            ]);

            foreach ($options as $option) {
                if ($option instanceof EcuOption) {
                    $option->setMasterOption(null);
                }
            }

            $entityManager->flush();
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
