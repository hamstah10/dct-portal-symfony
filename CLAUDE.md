# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**TFD CRM** — a Symfony 8.0 monolith for the internal administration, support, portal, and sales environment of Tuningfux. It combines an admin backoffice, customer/order management, vehicle database, support communication, credits, shop, API, AI, and portal functions in a single codebase.

## Common Commands

```bash
# Development
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
php bin/console make:entity
php bin/console debug:router

# Infrastructure
docker compose up -d          # Starts PostgreSQL + Mercure via compose.yaml

# Meilisearch indexing
php bin/console meili:import  # Re-index search indices

# Custom app commands
php bin/console app:cache:update
php bin/console app:credit:seed
php bin/console app:firecrawl:run
php bin/console app:import:dtc-catalog
php bin/console app:import:dtc-json
php bin/console app:import:shop-catalog
php bin/console app:support-email:fetch
php bin/console app:website-sniffer:run
php bin/console app:tool-news:run {provider} [--year=]   # Scrapes tool provider news (autotuner, magicmotorsport)
php bin/console app:tool-news:import {file}              # Imports scraped JSON into ToolNewsItem
php bin/console app:tool-news:analyze [options]           # Detects ECUs in tool news (--only-new, --force, --news-id=, --provider=)
php bin/console app:ecu-patterns:seed                     # Seeds default ECU detection patterns
```

There is currently no test suite. Verification is done via `php bin/console`, migrations, and manual UI testing.

## Architecture

### Technology Stack

- **Framework**: Symfony 8.0, PHP 8.4+
- **ORM**: Doctrine ORM with PHP attribute mapping, migrations in `migrations/`
- **Admin UI**: EasyAdmin with CRUD controllers extending `AbstractCrudController`
- **Frontend**: Twig + Symfony UX + AssetMapper/Importmap (Bootstrap 5.3, Stimulus controllers)
- **Search**: Meilisearch via `meilisearch/search-bundle` (indices: vehicle types/brands/series/models/engines, ECUs, customers, orders, DTC codes)
- **Realtime**: Mercure/SSE for order notifications
- **AI**: `symfony/ai-*` packages with OpenAI, Firecrawl, SerpAPI, YouTube tooling
- **Local package**: `tuningfux/instagram-bundle` as a path repository at `tuningfux-instagram-bundle/`

### Source Directory Structure

```
src/
  Controller/
    Admin/          109 CRUD and special controllers for EasyAdmin
    Api/            External API controllers (vehicle DB, Dyno, OpenClaw, etc.)
    *.php           Public-facing controllers (VehicleDatabase, Widget, Security, SPA)
  Entity/           All Doctrine entities (shared across systems)
  Repository/       Query classes per entity
  Service/          Business logic (VehicleApiProxy, SupportEmail, AI services, etc.)
  EasyAdmin/
    Field/          Custom EasyAdmin fields (CkEditor5Field)
    Filter/         Custom EasyAdmin filters (BrandMasterLogoFilter)
  Security/         Main firewall authenticators and user checkers
  Portals/
    Chiptuningfile/ Customer portal + React SPA (chiptuningfile system)
    Srfile/         Srfile portal system
    SuperadminPortal/ React-based superadmin portal
    Common/         Shared portal security helpers
  Websites/
    Chiptuningfile/ Public website cpt.tuningfux.de
  Brand/            Brand-specific common utilities
  Command/          Console commands
  EventSubscriber/
  Form/
  Message/          Symfony Messenger messages
  MessageHandler/
  Schedule.php      Symfony Scheduler
  Twig/             Custom Twig extensions
  Util/
```

### Multiple Parallel Systems (Firewalls)

The application runs four distinct systems with separate firewalls:

| System | Entry Point | Firewall | Role |
|--------|------------|---------|------|
| EasyAdmin Backoffice | `/admin`, `/login` | `main` | `ROLE_ADMIN` |
| Kunden-Portal (Widget) | `/kunden-widget` | `customer_widget` | `ROLE_WEBSITE_WIDGET_CUSTOMER` |
| Chiptuningfile Portal | portal request matcher | `chiptuningfile_portal` | `ROLE_PORTAL_CUSTOMER` |
| Srfile Portal | portal request matcher | `srfile_portal` | `ROLE_PORTAL_CUSTOMER` |

### Vehicle Data Pool / Context System

Two distinct access mechanisms for vehicle data — understanding which applies to a given system is critical:

**A. Direct pool via `Portal.vehicleDataPool`** — used by:
- Superadmin Portal (`src/Portals/SuperadminPortal/`)
- Chiptuningfile system (portal + website `cpt.tuningfux.de`)
- `VehicleApiProxy` automatically appends `poolKey` from the portal's pool

**B. Context-based via `VehicleDataContext`** — used by:
- External API clients (header/query `contextKey`)
- Public vehicle DB at `/chiptuning` and `/api/fahrzeugdatenbank/*` via `VehicleDataContextResolver`

**Website Widget** has its own mechanism: first `WebsiteWidgetConfig.vehicleDataPool`, then the widget's `ApiToken` context, then `live` fallback.

Key files: `src/Service/VehicleApiProxy.php`, `src/Service/VehicleDataContextResolver.php`, `src/Entity/VehicleDataPool.php`, `src/Entity/VehicleDataContext.php`.

See `docs/system-datenkontexte-pools.md` for the full decision matrix.

### Portals with React SPA Frontend

The Chiptuningfile and Srfile portals use React frontends:
- Source: `templates/portal/theme_react_chiptuningfile/chiptuningfilede/frontend/`
- Built output: `public/assets/portals/chiptuningfile/react/`
- Pages in `frontend/src/pages/`: Configurator, FileWizard, Dashboard, Tickets, Orders, Credits, etc.
- Backend host template: `templates/portal/theme_react_chiptuningfile/chiptuningfilede/backend/`

The React bundle must be rebuilt after frontend changes (see the portal README).

### Scraper Import System

Three connected admin areas for OLSX vehicle data import:
- `/admin/fahrzeugdatenbank/scraper` — scraper control
- `/admin/fahrzeugdatenbank/scraper/import` — classic root-file import (`scrapy-remote-scraper/olsx_brands_<type>_*.json`)
- `/admin/fahrzeugdatenbank/scraper/import-output` — output-file import (`scrapy-remote-scraper/data/output/<type>/*.json`)

Key files: `src/Controller/Admin/ScraperControlController.php`, `src/Controller/Admin/ScraperImportController.php`, `src/Controller/Admin/ScraperOutputImportController.php`, `src/Service/ScraperImportAnalyzer.php`.

### ECU Intelligence (Tool News Scraper)

Separate scraper/analysis pipeline for tool provider news (Autotuner, MagicMotorsport), independent from the OLSX vehicle scraper above — does not touch or depend on it. Scrapes news articles, imports them, then regex-detects ECU manufacturer/family/name, MCU, read method, protocol and vehicle hints with a confidence score. EasyAdmin section "ECU Intelligence": Tool-Anbieter, News-Quellen, Anbieter-News, Erkannte ECUs, Erkennungsmuster. Key files: `src/Service/ToolNews/EcuDetector.php`, `src/Command/ToolNewsRunCommand.php`, `src/Command/ToolNewsImportCommand.php`, `src/Command/ToolNewsAnalyzeCommand.php`, spiders in `scrapy-remote-scraper/scraper_project/spiders/{autotuner,magicmotorsport}_news_spider.py`. Full details and known EasyAdmin field pitfalls in `memory.md` (ECU Intelligence section).

### Website Widget System

Customers embed the vehicle database on their sites via a JS snippet without exposing API keys. Key files in `memory.md` (Widget section). Routes: `/widget/{embedKey}`, customer portal at `/kunden-widget`. Themes: `widget_premium_dark`, `widget_premium_light`, `layout5`, `layout6`.

## Code Conventions

- **PHP 8.4+** with strict type hints; no closing `?>` tags
- **Doctrine mapping**: PHP attributes (`#[ORM\Entity]`, `#[ORM\Column]`, etc.)
- **Entity setters** return `static` (fluent interface)
- **EasyAdmin CRUDs**: extend `AbstractCrudController`, implement `configureFields()`; use `.onlyOnIndex()`, `.hideOnForm()` etc. for field visibility
- **Collections**: `@var Collection<int, Entity>` phpdoc; `ArrayCollection` in constructor; fluent `add`/`remove` methods
- **Formatting**: 4-space indentation, LF line endings, UTF-8 (per `.editorconfig`)
- **German identifiers** are permitted for entity/field names (e.g. `Kunde`, `Auftrag`, `Kundennummer`)

## Key Configuration

- Environment via `.env` + `.env.local`; required vars include `DATABASE_URL`, `MEILISEARCH_URL`, `MEILISEARCH_KEY`, and keys for Mercure, Mailer, Lexware, Brevo, OpenAI, Firecrawl, Instagram, PayPal
- Meilisearch uses prefix `prod_` in production, `dev_` in dev (`config/packages/meilisearch.yaml`)
- Admin page access control uses `src/Service/AdminPageRegistry.php` alongside standard Symfony security
- `tuningfux/instagram-bundle` is a local path repository — changes there affect the main app directly

## Relevant Documentation

- `docs/system-uebersicht-portale-admins.md` — overview of all three main systems (EasyAdmin, Superadmin Portal, Chiptuningfile)
- `docs/system-datenkontexte-pools.md` — vehicle data pool/context decision matrix
- `docs/api.md` — API documentation
- `docs/fahrzeugdatenbank-api-einbindung.md` — vehicle DB API integration guide
- `memory.md` — running project memory with module-specific implementation notes
