<?php

namespace App\Service;

final class AdminPageRegistry
{
    public const DASHBOARD = 'dashboard';
    private const LABEL_SEPARATOR = ' / ';

    public static function routeKey(string $routeName): string
    {
        return 'route:' . $routeName;
    }

    public static function crudKey(string $crudControllerFqcn): string
    {
        return 'crud:' . $crudControllerFqcn;
    }

    /**
     * @return array<string, string>
     */
    public function getPages(): array
    {
        return self::pages();
    }

    /**
     * @return array<string, string>
     */
    public function getChoices(): array
    {
        $choices = [];
        foreach (self::pages() as $key => $label) {
            $choices[$label] = $key;
        }

        return $choices;
    }

    public function getLabel(string $pageKey): string
    {
        return self::pages()[$pageKey] ?? $pageKey;
    }

    /**
     * @return list<string>
     */
    public function getChildPageKeys(string $pageKey): array
    {
        $children = [];
        foreach (self::pages() as $key => $label) {
            if ($key === $pageKey) {
                continue;
            }

            if (\in_array($pageKey, $this->getParentMenuKeys($key), true)) {
                $children[] = $key;
            }
        }

        return $children;
    }

    /**
     * @return list<string>
     */
    public function getParentMenuKeys(string $pageKey): array
    {
        $label = $this->getLabel($pageKey);
        $segments = array_values(array_filter(explode(self::LABEL_SEPARATOR, $label), 'strlen'));
        if (\count($segments) <= 1) {
            return $this->extraParentMenuKeys()[$pageKey] ?? [];
        }

        $menuLabelMap = self::menuLabelMap();
        $parents = [];
        $pathParts = [];

        $limit = \count($segments) - 1;
        for ($i = 0; $i < $limit; $i++) {
            $pathParts[] = $segments[$i];
            $path = implode(self::LABEL_SEPARATOR, $pathParts);
            $normalizedPath = strtolower($path);
            if (isset($menuLabelMap[$normalizedPath])) {
                $parents[] = $menuLabelMap[$normalizedPath];
            }
        }

        $extraParents = $this->extraParentMenuKeys()[$pageKey] ?? [];
        if ($extraParents) {
            $parents = array_merge($extraParents, $parents);
        }

        return array_values(array_unique($parents));
    }

    /**
     * @return array<string, string>
     */
    private static function pages(): array
    {
        return self::menuPages() + [
            self::DASHBOARD => 'Dashboard',
            self::routeKey('admin_journal') => 'Dashboard / Journal',
            self::routeKey('admin_statistics') => 'Arbeit / Statistiken',
            self::routeKey('admin_search') => 'Suche / Meilisearch',
            self::routeKey('admin_search_api') => 'Suche / Meilisearch',
            self::routeKey('admin_search_chat') => 'Suche / Chat Suche',
            self::routeKey('admin_search_chat_api') => 'Suche / Chat Suche',
            self::routeKey('admin_revenue_statistics') => 'Arbeit / Statistiken / Umsatz',
            self::crudKey(\App\Controller\Admin\AuftragCrudController::class) => 'Arbeit / Aufträge',
            self::routeKey('admin_communication') => 'Arbeit / Kommunikation',
            self::routeKey('admin_support_tickets') => 'Arbeit / Tickets',
            self::routeKey('admin_support_email') => 'Arbeit / E-Mail',
            self::crudKey(\App\Controller\Admin\KundeCrudController::class) => 'Arbeit / Kunden',
            self::crudKey(\App\Controller\Admin\LeadCrudController::class) => 'Leads / Leads',
            self::crudKey(\App\Controller\Admin\KundeCreditTransactionCrudController::class) => 'Finanzen / Credits / Historie',

            self::routeKey('admin_vehicle_database_overview') => 'Fahrzeugdatenbank / Fahrzeuge / Uebersicht',
            self::routeKey('admin_vehicle_usage_statistics') => 'Fahrzeugdatenbank / Fahrzeuge / Statistik',
            self::routeKey('admin_scraper_control') => 'Fahrzeugdatenbank / Scraper',
            self::routeKey('admin_scraper_import') => 'Fahrzeugdatenbank / Scraper / Import',
            self::routeKey('admin_scraper_output_import') => 'Fahrzeugdatenbank / Scraper / Import Output',
            self::routeKey('admin_tool_news_control') => 'ECU Intelligence / Steuerung',
            self::crudKey(\App\Controller\Admin\ToolProviderCrudController::class) => 'ECU Intelligence / Tool-Anbieter',
            self::crudKey(\App\Controller\Admin\ToolNewsSourceCrudController::class) => 'ECU Intelligence / News-Quellen',
            self::crudKey(\App\Controller\Admin\ToolNewsItemCrudController::class) => 'ECU Intelligence / Anbieter-News',
            self::crudKey(\App\Controller\Admin\DetectedEcuCrudController::class) => 'ECU Intelligence / Erkannte ECUs',
            self::crudKey(\App\Controller\Admin\EcuDetectionPatternCrudController::class) => 'ECU Intelligence / Erkennungsmuster',
            self::crudKey(\App\Controller\Admin\VehicleCoverageMatchCrudController::class) => 'ECU Intelligence / Fahrzeug-Matches',
            self::crudKey(\App\Controller\Admin\VehiclePlatformAliasCrudController::class) => 'ECU Intelligence / Plattform-Aliase',
            self::crudKey(\App\Controller\Admin\VehicleTypeCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Fahrzeugtypen',
            self::crudKey(\App\Controller\Admin\VehicleBrandCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Marken',
            self::crudKey(\App\Controller\Admin\BrandMasterCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / Brand-Master',
            self::crudKey(\App\Controller\Admin\PerformanceBrandCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / Sportmarke',
            self::crudKey(\App\Controller\Admin\VehicleSeriesCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Baureihen',
            self::crudKey(\App\Controller\Admin\VehicleModelCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Modelle',
            self::crudKey(\App\Controller\Admin\VehicleEngineCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Motoren',
            self::crudKey(\App\Controller\Admin\AntriebsartCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Antriebsarten',
            self::crudKey(\App\Controller\Admin\VehicleStageCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stages',
            self::routeKey('admin_vehicle_gearbox') => 'Fahrzeugdatenbank / Fahrzeuge / Getriebe',
            self::crudKey(\App\Controller\Admin\GearboxTypeCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Getriebe-Typen',
            self::crudKey(\App\Controller\Admin\GearboxCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Getriebe',
            self::routeKey('admin_vehicle_ecu') => 'Fahrzeugdatenbank / Fahrzeuge / ECU',
            self::routeKey('admin_ecu_ecu_assign_master') => 'Fahrzeugdatenbank / Fahrzeuge / ECU',
            self::crudKey(\App\Controller\Admin\EcuManufacturerCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / ECU-Hersteller',
            self::crudKey(\App\Controller\Admin\EcuManufacturerMasterCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / ECU-Hersteller-Master',
            self::crudKey(\App\Controller\Admin\EcuCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / ECUs',
            self::crudKey(\App\Controller\Admin\EcuMasterCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / ECU-Master',
            self::crudKey(\App\Controller\Admin\EcuOptionCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / ECU-Optionen',
            self::crudKey(\App\Controller\Admin\EcuOptionMasterCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / ECU-Option-Master',
            self::crudKey(\App\Controller\Admin\EcuOptionHintCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Stammdaten / ECU-Optionen Hinweise',
            self::routeKey('admin_vehicle_tools') => 'Fahrzeugdatenbank / Fahrzeuge / Tools',
            self::crudKey(\App\Controller\Admin\ToolManufacturerCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Tool-Hersteller',
            self::crudKey(\App\Controller\Admin\ToolMethodeCrudController::class) => 'Fahrzeugdatenbank / Fahrzeuge / Tool-Methoden',

            self::crudKey(\App\Controller\Admin\ProfitCalculatorCrudController::class) => 'Tools / Profitcalculator / Calculator',
            self::crudKey(\App\Controller\Admin\ProfitCalculatorVehicleTypeCrudController::class) => 'Tools / Profitcalculator / Fahrzeugtypen',
            self::crudKey(\App\Controller\Admin\ProfitCalculatorOptionCrudController::class) => 'Tools / Profitcalculator / Optionen',
            self::crudKey(\App\Controller\Admin\ProfitCalculatorPriceTierCrudController::class) => 'Tools / Profitcalculator / Preisstaffeln',

            self::routeKey('admin_dyno_stats') => 'Tools / Dyno / Statistik',
            self::crudKey(\App\Controller\Admin\DynoVehicleCrudController::class) => 'Tools / Dyno / Fahrzeuge',
            self::crudKey(\App\Controller\Admin\DynoCustomerCrudController::class) => 'Tools / Dyno / Kunden',
            self::crudKey(\App\Controller\Admin\DynoTripCrudController::class) => 'Tools / Dyno / Fahrten',
            self::crudKey(\App\Controller\Admin\DynoSprintCrudController::class) => 'Tools / Dyno / Sprints',
            self::crudKey(\App\Controller\Admin\DynoDtcCrudController::class) => 'Tools / Dyno / DTCs',

            self::crudKey(\App\Controller\Admin\DtcCategoryCrudController::class) => 'Tools / Fehlercodes / Kategorien',
            self::crudKey(\App\Controller\Admin\DtcCodeCrudController::class) => 'Tools / Fehlercodes / Codes',
            self::crudKey(\App\Controller\Admin\SoftwareCrudController::class) => 'Tools / Software',
            self::routeKey('admin_vin_decoder') => 'Tools / VIN-Decoder',
            self::routeKey('admin_vpic_explorer') => 'Tools / NHTSA vPIC',
            self::routeKey('admin_test_actions') => 'Tools / TestActions',
            self::routeKey('socialfux_app') => 'Tools / SocialFux',
            self::routeKey('admin_projects_todo') => 'Projekte / ToDo',
            self::routeKey('admin_projects_ideas') => 'Projekte / Ideen',
            self::crudKey(\App\Controller\Admin\AiProjectCrudController::class) => 'AI / Projekte',
            self::routeKey('admin_ai_project_workspace') => 'AI / Projekte / Workspace',
            self::routeKey('admin_ai_chat') => 'AI / Chat',
            self::routeKey('admin_ai_agent') => 'AI / Agent',
            self::routeKey('admin_ai_firecrawl') => 'AI / Firecrawl',
            self::routeKey('admin_ai_dalle') => 'AI / DALL-E',
            self::routeKey('admin_ai_image_analysis') => 'AI / Bildanalyse',
            self::routeKey('admin_ai_pdf_analysis') => 'AI / PDF-Analyse',
            self::routeKey('admin_ai_vehicle') => 'AI / Fahrzeugdatenbank',

            self::crudKey(\App\Controller\Admin\ShopProductCrudController::class) => 'eCommerce / Katalog / Produkte',
            self::crudKey(\App\Controller\Admin\ShopCategoryCrudController::class) => 'eCommerce / Katalog / Kategorien',
            self::crudKey(\App\Controller\Admin\ShopManufacturerCrudController::class) => 'eCommerce / Katalog / Hersteller',
            self::crudKey(\App\Controller\Admin\ShopTagCrudController::class) => 'eCommerce / Katalog / Tags',
            self::crudKey(\App\Controller\Admin\ShopDownloadCrudController::class) => 'eCommerce / Katalog / Downloads',
            self::crudKey(\App\Controller\Admin\ShopVideoCrudController::class) => 'eCommerce / Katalog / Videos',
            self::crudKey(\App\Controller\Admin\ShopProductOptionCrudController::class) => 'eCommerce / Katalog / Optionen',
            self::crudKey(\App\Controller\Admin\ShopProductOptionValueCrudController::class) => 'eCommerce / Katalog / Optionswerte',
            self::crudKey(\App\Controller\Admin\ShopProductDeliveryScopeCrudController::class) => 'eCommerce / Katalog / Lieferumfang',
            self::crudKey(\App\Controller\Admin\ShopProductLinkCrudController::class) => 'eCommerce / Katalog / Produktlinks',
            self::crudKey(\App\Controller\Admin\ShopTaxCrudController::class) => 'eCommerce / Einstellungen / Steuern',
            self::crudKey(\App\Controller\Admin\ShopShippingMethodCrudController::class) => 'eCommerce / Einstellungen / Versandarten',
            self::crudKey(\App\Controller\Admin\ShopPaymentMethodCrudController::class) => 'eCommerce / Einstellungen / Zahlungsarten',
            self::crudKey(\App\Controller\Admin\ShopOrderCrudController::class) => 'eCommerce / Bestellungen / Bestellungen',
            self::routeKey('admin_shop_statistics') => 'eCommerce / Statistik',

            self::crudKey(\App\Controller\Admin\MarketingAdSizeCrudController::class) => 'Marketing / Anzeigen',
            self::crudKey(\App\Controller\Admin\MarketingCategoryCrudController::class) => 'Marketing / Kategorien',

            self::routeKey('admin_media_manager') => 'Media / Media / Manager',
            self::crudKey(\App\Controller\Admin\MediaFolderCrudController::class) => 'Media / Media / Ordner',
            self::crudKey(\App\Controller\Admin\MediaAssetCrudController::class) => 'Media / Media / Dateien',
            self::crudKey(\App\Controller\Admin\TemplateCrudController::class) => 'Media / Websites / Templates',
            self::crudKey(\App\Controller\Admin\WebsiteSnifferCrudController::class) => 'Media / Websites / Sniffer',
            self::crudKey(\App\Controller\Admin\DemoCrudController::class) => 'Media / Websites / Demos',
            self::crudKey(\App\Controller\Admin\WebsiteWidgetConfigCrudController::class) => 'Website Widget / Website Widget',

            self::routeKey('admin_credits_statistics') => 'Finanzen / Credits / Statistik',
            self::crudKey(\App\Controller\Admin\CreditPackageCrudController::class) => 'Finanzen / Credits / Pakete',
            self::crudKey(\App\Controller\Admin\CreditCategoryCrudController::class) => 'Finanzen / Credits / Kategorien',

            self::routeKey('admin_abo_statistics') => 'Finanzen / Abo / Statistik',
            self::crudKey(\App\Controller\Admin\AboCrudController::class) => 'Finanzen / Abo / Aktiv',
            self::crudKey(\App\Controller\Admin\AboAuswahlCrudController::class) => 'Finanzen / Abo / Erstellen',
            self::crudKey(\App\Controller\Admin\ZahlungsartCrudController::class) => 'Finanzen / Zahlungsarten',
            self::routeKey('admin_lexoffice') => 'Finanzen / Lexoffice',

            self::crudKey(\App\Controller\Admin\VehicleFrontendSettingsCrudController::class) => 'Verwaltung / Einstellungen / Konfigurator',
            self::crudKey(\App\Controller\Admin\FirmaCrudController::class) => 'Verwaltung / Einstellungen / Firmen',
            self::crudKey(\App\Controller\Admin\PortalCrudController::class) => 'Verwaltung / Einstellungen / Portale',
            self::crudKey(\App\Controller\Admin\PortalPriceListCrudController::class) => 'Verwaltung / Einstellungen / Portal-Preislisten',
            self::crudKey(\App\Controller\Admin\PortalPriceListItemCrudController::class) => 'Verwaltung / Einstellungen / Portal-Preisliste Positionen',
            self::crudKey(\App\Controller\Admin\TextbausteinCrudController::class) => 'Verwaltung / Einstellungen / Textbausteine',

            self::crudKey(\App\Controller\Admin\ZugangsdatenCrudController::class) => 'Verwaltung / Security / Zugangsdaten',
            self::crudKey(\App\Controller\Admin\ApiTokenCrudController::class) => 'Verwaltung / Security / API Tokens',
            self::crudKey(\App\Controller\Admin\WebhookEndpointCrudController::class) => 'Verwaltung / Security / Webhooks',
            self::crudKey(\App\Controller\Admin\AdminPageAccessCrudController::class) => 'Verwaltung / Security / Seitenrechte',

            self::crudKey(\App\Controller\Admin\EmailKontoCrudController::class) => 'Verwaltung / E-Mail / Konfiguration',
            self::crudKey(\App\Controller\Admin\EmailLabelCrudController::class) => 'Verwaltung / E-Mail / Labels',
            self::routeKey('admin_support_newsletter') => 'Verwaltung / E-Mail / Newsletter',

            self::crudKey(\App\Controller\Admin\UserCrudController::class) => 'Verwaltung / Benutzer / Anzeigen',
            self::crudKey(\App\Controller\Admin\UserGroupCrudController::class) => 'Verwaltung / Benutzer / Gruppe anlegen',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function menuPages(): array
    {
        return [
            'menu:arbeit' => 'ARbeit',
            'menu:leads' => 'Leads',
            'menu:fahrzeugdatenbank' => 'Fahrzeugdatenbank',
            'menu:fahrzeugdatenbank/fahrzeuge' => 'Fahrzeugdatenbank / Fahrzeuge',
            'menu:tools' => 'Tools',
            'menu:tools/profitcalculator' => 'Tools / Profitcalculator',
            'menu:tools/dyno' => 'Tools / Dyno',
            'menu:tools/fehlercodes' => 'Tools / Fehlercodes',
            'menu:projekte' => 'Projekte',
            'menu:ecommerce' => 'eCommerce',
            'menu:ecommerce/katalog' => 'eCommerce / Katalog',
            'menu:ecommerce/einstellungen' => 'eCommerce / Einstellungen',
            'menu:ecommerce/bestellungen' => 'eCommerce / Bestellungen',
            'menu:marketing' => 'Marketing',
            'menu:ai' => 'AI',
            'menu:media' => 'Media',
            'menu:media/media' => 'Media / Media',
            'menu:media/websites' => 'Media / Websites',
            'menu:website-widget' => 'Website Widget',
            'menu:finanzen' => 'Finanzen',
            'menu:finanzen/credits' => 'Finanzen / Credits',
            'menu:finanzen/abo' => 'Finanzen / Abo',
            'menu:verwaltung' => 'Verwaltung',
            'menu:verwaltung/einstellungen' => 'Verwaltung / Einstellungen',
            'menu:verwaltung/security' => 'Verwaltung / Security',
            'menu:verwaltung/email' => 'Verwaltung / E-Mail',
            'menu:verwaltung/benutzer' => 'Verwaltung / Benutzer',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function menuLabelMap(): array
    {
        static $map = null;
        if (null !== $map) {
            return $map;
        }

        $map = [];
        foreach (self::menuPages() as $key => $label) {
            $map[strtolower($label)] = $key;
        }

        return $map;
    }

    /**
     * @return array<string, list<string>>
     */
    private function extraParentMenuKeys(): array
    {
        return [
            self::crudKey(\App\Controller\Admin\KundeCreditTransactionCrudController::class) => [
                'menu:arbeit',
            ],
        ];
    }
}
