<?php

namespace App\Controller\Admin;

use App\Entity\AdminPageAccess;
use App\Repository\AdminPageAccessRepository;
use App\Service\AdminPageRegistry;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/seitenrechte', routeName: 'admin_page_access')]
final class AdminPageAccessCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminPageRegistry $pageRegistry,
        private readonly AdminPageAccessRepository $pageAccessRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return AdminPageAccess::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Seitenrecht')
            ->setEntityLabelInPlural('Seitenrechte')
            ->setDefaultSort(['pageKey' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $currentKey = null;
        $context = $this->getContext();
        if (null !== $context) {
            $instance = $context->getEntity()?->getInstance();
            if ($instance instanceof AdminPageAccess) {
                $currentKey = $instance->getPageKey();
            }
        }

        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('pageKey', 'Seite')
                ->onlyOnIndex()
                ->formatValue(fn (mixed $value): string => $this->pageRegistry->getLabel((string) $value)),
            BooleanField::new('enabled', 'Aktiv'),
            BooleanField::new('includeChildren', 'Unterseiten einbeziehen')
                ->onlyOnForms()
                ->setHelp('Wenn aktiv, gilt diese Regel auch fuer Unterseiten/Unterpunkte.'),
            ArrayField::new('allowedGroups', 'Sichtbar fuer Gruppen')
                ->onlyOnIndex()
                ->formatValue(static function (mixed $value, ?AdminPageAccess $access): string {
                    if (!$access instanceof AdminPageAccess) {
                        return '';
                    }

                    $names = [];
                    foreach ($access->getAllowedGroups() as $group) {
                        $names[] = (string) $group;
                    }

                    if ([] === $names) {
                        return 'Alle Admins';
                    }

                    return implode(', ', $names);
                }),
            AssociationField::new('allowedGroups', 'Sichtbar fuer Gruppen')
                ->onlyOnForms()
                ->setFormTypeOptions(['by_reference' => false])
                ->setHelp('Leer = sichtbar fuer alle Admins.'),
        ];

        if (Crud::PAGE_NEW === $pageName) {
            array_splice($fields, 1, 0, [
                ChoiceField::new('pageKeys', 'Seiten')
                    ->setFormTypeOption('mapped', false)
                    ->allowMultipleChoices()
                    ->setChoices($this->buildAvailableChoices($currentKey))
                    ->setRequired(true)
                    ->setHelp('Mehrfachauswahl erstellt mehrere Regeln gleichzeitig.'),
            ]);
        }

        if (Crud::PAGE_EDIT === $pageName) {
            array_splice($fields, 1, 0, [
                ChoiceField::new('pageKey', 'Seite')
                    ->setChoices($this->buildAvailableChoices($currentKey))
                    ->setRequired(true),
            ]);
        }

        return $fields;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof AdminPageAccess) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $request = $this->getContext()?->getRequest();
        $selectedKeys = $request?->request->all('AdminPageAccess')['pageKeys'] ?? [];
        if (!\is_array($selectedKeys)) {
            $selectedKeys = [];
        }

        $selectedKeys = array_values(array_filter($selectedKeys, static fn (mixed $value): bool => is_string($value) && '' !== $value));
        if ([] === $selectedKeys) {
            $currentKey = $entityInstance->getPageKey();
            if ('' !== $currentKey) {
                $selectedKeys = [$currentKey];
            }
        }
        if ([] === $selectedKeys) {
            return;
        }

        $firstKey = array_shift($selectedKeys);
        $entityInstance->setPageKey($firstKey);
        $entityManager->persist($entityInstance);

        foreach ($selectedKeys as $pageKey) {
            $clone = new AdminPageAccess();
            $clone->setPageKey($pageKey);
            $clone->setEnabled($entityInstance->isEnabled());
            $clone->setIncludeChildren($entityInstance->isIncludeChildren());
            foreach ($entityInstance->getAllowedGroups() as $group) {
                $clone->addAllowedGroup($group);
            }

            $entityManager->persist($clone);
        }

        $entityManager->flush();
    }

    /**
     * @return array<string, string>
     */
    private function buildAvailableChoices(?string $currentKey): array
    {
        $usedKeys = [];
        foreach ($this->pageAccessRepository->findAll() as $rule) {
            $usedKeys[] = $rule->getPageKey();
            if ($rule->isIncludeChildren()) {
                $usedKeys = array_merge($usedKeys, $this->pageRegistry->getChildPageKeys($rule->getPageKey()));
            }
        }
        $usedKeys = array_values(array_unique($usedKeys));
        if (null !== $currentKey) {
            $usedKeys = array_diff($usedKeys, [$currentKey]);
        }

        $choices = $this->pageRegistry->getChoices();
        foreach ($choices as $label => $key) {
            if (\in_array($key, $usedKeys, true)) {
                unset($choices[$label]);
            }
        }

        return $choices;
    }
}
