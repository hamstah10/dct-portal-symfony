<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310202000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add chiptuning profit calculator tables and seed initial data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `tx_chiptuningprofitcalculator_domain_model_calculator` (
            `uid` int NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `vat_rate` decimal(5,2) NOT NULL,
            `purchase_price_include_vat` tinyint NOT NULL,
            `end_price_include_vat` tinyint NOT NULL,
            `options_template` varchar(64) DEFAULT NULL,
            PRIMARY KEY (`uid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE `tx_chiptuningprofitcalculator_domain_model_vehicletype` (
            `uid` int NOT NULL AUTO_INCREMENT,
            `calculator` int DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `identifier` varchar(255) DEFAULT NULL,
            `end_price` decimal(10,2) NOT NULL,
            `purchase_price` decimal(10,2) NOT NULL,
            `purchase_price_include_vat` tinyint NOT NULL,
            `end_price_include_vat` tinyint NOT NULL,
            `icon_media_asset_id` int DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `options` int NOT NULL,
            PRIMARY KEY (`uid`),
            KEY `IDX_PROFITCALC_VEHICLETYPE_CALCULATOR` (`calculator`),
            KEY `IDX_PROFITCALC_VEHICLETYPE_ICON_MEDIA` (`icon_media_asset_id`),
            CONSTRAINT `FK_PROFITCALC_VEHICLETYPE_CALCULATOR` FOREIGN KEY (`calculator`) REFERENCES `tx_chiptuningprofitcalculator_domain_model_calculator` (`uid`) ON DELETE SET NULL,
            CONSTRAINT `FK_PROFITCALC_VEHICLETYPE_ICON_MEDIA` FOREIGN KEY (`icon_media_asset_id`) REFERENCES `media_asset` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE `tx_chiptuningprofitcalculator_domain_model_option` (
            `uid` int NOT NULL AUTO_INCREMENT,
            `vehicle_type` int DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `description` longtext DEFAULT NULL,
            `price` decimal(10,2) NOT NULL,
            `purchase_price` decimal(10,2) NOT NULL,
            `end_price` decimal(10,2) NOT NULL,
            `end_price_include_vat` tinyint NOT NULL,
            `icon_media_asset_id` int DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`uid`),
            KEY `IDX_PROFITCALC_OPTION_VEHICLETYPE` (`vehicle_type`),
            KEY `IDX_PROFITCALC_OPTION_ICON_MEDIA` (`icon_media_asset_id`),
            CONSTRAINT `FK_PROFITCALC_OPTION_VEHICLETYPE` FOREIGN KEY (`vehicle_type`) REFERENCES `tx_chiptuningprofitcalculator_domain_model_vehicletype` (`uid`) ON DELETE SET NULL,
            CONSTRAINT `FK_PROFITCALC_OPTION_ICON_MEDIA` FOREIGN KEY (`icon_media_asset_id`) REFERENCES `media_asset` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE `tx_chiptuningprofitcalculator_domain_model_pricetier` (
            `uid` int NOT NULL AUTO_INCREMENT,
            `credits` int NOT NULL,
            `purchase_price` decimal(10,2) NOT NULL,
            `price_per_file` decimal(10,2) NOT NULL,
            `is_default_price` tinyint NOT NULL,
            PRIMARY KEY (`uid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql("INSERT INTO `tx_chiptuningprofitcalculator_domain_model_calculator`
            (`uid`, `title`, `vat_rate`, `purchase_price_include_vat`, `end_price_include_vat`, `options_template`) VALUES
            (1, 'Chiptuning', 19.00, 0, 1, 'grid')");

        $this->addSql("INSERT INTO `tx_chiptuningprofitcalculator_domain_model_vehicletype`
            (`uid`, `calculator`, `title`, `identifier`, `end_price`, `purchase_price`, `purchase_price_include_vat`, `end_price_include_vat`, `icon_media_asset_id`, `image`, `options`) VALUES
            (1, 1, 'PKW', 'pkw-calculator', 599.00, 115.00, 0, 1, NULL, NULL, 15),
            (2, 1, 'LKW', 'lkw-calculator', 899.00, 200.00, 0, 1, NULL, NULL, 3),
            (4, 1, 'Bike', 'bike-calculator', 499.00, 100.00, 0, 1, NULL, NULL, 1)");

        $this->addSql("INSERT INTO `tx_chiptuningprofitcalculator_domain_model_pricetier`
            (`uid`, `credits`, `purchase_price`, `price_per_file`, `is_default_price`) VALUES
            (1, 100, 115.00, 115.00, 1),
            (2, 500, 550.00, 110.00, 0),
            (3, 1000, 1050.00, 105.00, 0),
            (4, 2000, 2000.00, 100.00, 0),
            (5, 3000, 2850.00, 95.00, 0),
            (6, 4000, 3600.00, 90.00, 0),
            (7, 5000, 4250.00, 85.00, 0)");

        $this->addSql("INSERT INTO `tx_chiptuningprofitcalculator_domain_model_option`
            (`uid`, `vehicle_type`, `title`, `description`, `price`, `purchase_price`, `end_price`, `end_price_include_vat`, `icon_media_asset_id`, `image`) VALUES
            (1, 1, 'Pop&Bang auf Sport Button', 'Die \"Pop & Bang\"-Option erzeugt beim Verzögern knisternde Geräusche und Verpuffungen im Auspuff, was bei Tuningfans beliebt ist. Sie sorgt für sportlichen Sound und kleine Flammen, ist jedoch meist nur für den Einsatz abseits der Straße geeignet.', 0.00, 120.00, 250.00, 1, NULL, NULL),
            (2, 1, 'Drallklappen / Flaps off', NULL, 0.00, 50.00, 150.00, 1, NULL, NULL),
            (3, 2, 'AdBlue off', NULL, 0.00, 100.00, 300.00, 1, NULL, NULL),
            (4, 2, 'DPF off', NULL, 0.00, 60.00, 57.50, 1, NULL, NULL),
            (5, 2, 'Agr off', NULL, 0.00, 50.00, 57.50, 1, NULL, NULL),
            (7, 4, 'Auspuffklappen', NULL, 0.00, 50.00, 200.00, 1, NULL, NULL),
            (8, 1, 'Vmax OFF', 'Die Option \"Vmax Off\" deaktiviert den elektronischen Höchstgeschwindigkeitsbegrenzer des Herstellers, sodass das Fahrzeug sein volles Geschwindigkeitspotenzial entfalten kann. Ideal für Rennstrecken oder Bereiche ohne Geschwindigkeitsbegrenzungen, ermöglicht sie die volle Motorleistung und sorgt für ein aufregenderes Fahrerlebnis.', 0.00, 50.00, 150.00, 1, NULL, NULL),
            (9, 1, 'Cylinder-On-Demand-OFF', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (10, 1, 'DPF-OFF', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (11, 1, 'Cold-Start-OFF', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (12, 1, 'Decat-Cat-OFF', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (13, 1, 'DPF-OFF', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (14, 1, 'DSG-farts', 'Die Option \"Cold Start OFF\" optimiert den Motorstart bei niedrigen Temperaturen, indem sie die übliche Drehzahlerhöhung deaktiviert, die den Katalysator schneller aufwärmt. Dies sorgt für einen leiseren und weniger aggressiven Start, ideal für Kunden, die Lärm vermeiden möchten, insbesondere bei Sportauspuffanlagen. Sie wird vor allem für Rennwagen oder modifizierte Fahrzeuge empfohlen, insbesondere in Umgebungen mit weniger strengen Emissionsvorschriften.', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (15, 1, 'DTC-OFF', '', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (16, 1, 'EGR-OFF', '', 0.00, 50.00, 200.00, 1, NULL, NULL),
            (17, 1, 'Exhaust-Flaps', '', 0.00, 100.00, 250.00, 1, NULL, NULL),
            (18, 1, 'GPF-OFF', '', 0.00, 50.00, 150.00, 1, NULL, NULL),
            (19, 1, 'Hard-Rev-Cut', '', 0.00, 50.00, 100.00, 1, NULL, NULL),
            (20, 1, 'Hot-start-fix', '', 0.00, 50.00, 100.00, 1, NULL, NULL)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `tx_chiptuningprofitcalculator_domain_model_option`');
        $this->addSql('DROP TABLE `tx_chiptuningprofitcalculator_domain_model_vehicletype`');
        $this->addSql('DROP TABLE `tx_chiptuningprofitcalculator_domain_model_pricetier`');
        $this->addSql('DROP TABLE `tx_chiptuningprofitcalculator_domain_model_calculator`');
    }
}
