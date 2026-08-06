<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Service;

use App\Entity\Portal;

final class SuperadminPortalBootstrapBuilder
{
    /**
     * @var array<string, array{label: string, path: string}>
     */
    private const OPERATIONS_NAVIGATION = [
        Portal::MODULE_OPS_OVERVIEW => ['label' => 'Übersicht', 'path' => '/operations'],
        Portal::MODULE_OPS_ORDERS => ['label' => 'Aufträge', 'path' => '/operations/orders'],
        Portal::MODULE_OPS_VEHICLES => ['label' => 'Chiptuning', 'path' => '/operations/vehicles'],
        Portal::MODULE_OPS_SEARCH => ['label' => 'Suche', 'path' => '/operations/search'],
        Portal::MODULE_OPS_CUSTOMERS => ['label' => 'Kunden', 'path' => '/operations/customers'],
        Portal::MODULE_OPS_TICKETS => ['label' => 'Tickets', 'path' => '/operations/tickets'],
        Portal::MODULE_OPS_EMAIL => ['label' => 'E-Mail', 'path' => '/operations/email'],
        Portal::MODULE_OPS_CALENDAR => ['label' => 'Kalender', 'path' => '/operations/calendar'],
        Portal::MODULE_OPS_INSTALLATION => ['label' => 'Installation', 'path' => '/operations/installation'],
        Portal::MODULE_OPS_DSG => ['label' => 'Getriebe', 'path' => '/operations/dsg'],
        Portal::MODULE_OPS_CONTACT => ['label' => 'Kontakt', 'path' => '/operations/contact'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(Portal $portal): array
    {
        return [
            'portal' => [
                'id' => $portal->getId(),
                'key' => $portal->getPortalKey(),
                'name' => $portal->getName(),
                'type' => $portal->getPortalType(),
                'host' => $portal->getPortalHost(),
                'websiteHost' => $portal->getWebsiteHost(),
                'enabledModules' => $portal->getEnabledModules(),
                'logoPath' => $portal->getLogoPath(),
            ],
            'portalDesign' => $portal->getResolvedDesign(),
            'navigation' => $this->buildNavigation($portal),
            'operationsNavigation' => $this->buildOperationsNavigation($portal),
            'dashboard' => [
                'title' => $portal->getName() ?: 'Superadmin Portal',
                'description' => $portal->getDescription() ?: 'React Portal Shell fuer eine eigenstaendige Superadmin-Ansicht.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildNavigation(Portal $portal): array
    {
        $choiceMap = array_flip(Portal::getModuleChoices());
        $items = [];

        foreach ($portal->getEnabledModules() as $module) {
            $label = $choiceMap[$module] ?? $this->humanize($module);
            $path = $module === Portal::MODULE_DASHBOARD ? '/' : '/' . trim(str_replace('_', '-', $module), '/');

            $items[] = [
                'key' => $module,
                'label' => $label,
                'path' => $path,
            ];
        }

        if ($items === []) {
            $items[] = [
                'key' => Portal::MODULE_DASHBOARD,
                'label' => 'Dashboard',
                'path' => '/',
            ];
        }

        return $items;
    }

    /**
     * @return list<array{key: string, label: string, path: string}>
     */
    private function buildOperationsNavigation(Portal $portal): array
    {
        $items = [];

        foreach ($portal->getEnabledModules() as $module) {
            if (!isset(self::OPERATIONS_NAVIGATION[$module])) {
                continue;
            }

            $config = self::OPERATIONS_NAVIGATION[$module];

            $items[] = [
                'key' => $module,
                'label' => $config['label'],
                'path' => $config['path'],
            ];
        }

        if ($items !== []) {
            return $items;
        }

        return array_map(
            static fn (string $key, array $config): array => [
                'key' => $key,
                'label' => $config['label'],
                'path' => $config['path'],
            ],
            array_keys(self::OPERATIONS_NAVIGATION),
            array_values(self::OPERATIONS_NAVIGATION)
        );
    }

    private function humanize(string $value): string
    {
        $normalized = trim(str_replace(['-', '_'], ' ', $value));
        if ($normalized === '') {
            return 'Modul';
        }

        return mb_convert_case($normalized, \MB_CASE_TITLE, 'UTF-8');
    }
}
