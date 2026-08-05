# Merknotiz: Ecommerce-API fuer Schwesterportal

Stand: 17.07.2026

Diese Aenderungen wurden im Portal umgesetzt und sollen spaeter ins Schwesterportal uebertragen werden.

## Ecommerce-Katalog-API

- Neue geschuetzte Read-only-API unter `/api/ecommerce`.
- Authentifizierung ueber bestehenden `ApiToken`:
  - Header: `Authorization: Bearer <API_TOKEN>`
  - Token muss aktiv und nicht abgelaufen sein.
  - Kein `X-Token-Id` noetig.
- Neue Endpunkte:
  - `GET /api/ecommerce`
  - `GET /api/ecommerce/products`
  - `GET /api/ecommerce/products/{uid}`
  - `GET /api/ecommerce/categories`
  - `GET /api/ecommerce/manufacturers`
- Produktliste unterstuetzt:
  - `q`
  - `category`
  - `manufacturer`
  - `page`
  - `limit`

## Wichtige Dateien

- `config/routes.yaml`
- `src/Controller/Api/EcommerceApiController.php`
- `src/Service/EcommercePayloadMapper.php`
- `src/Repository/ShopProductRepository.php`
- `src/Repository/ShopCategoryRepository.php`
- `src/Repository/ShopManufacturerRepository.php`
- `src/Repository/ShopProductLinkRepository.php`
- `docs/ecommerce-api.md`
- `docs/api.md`

## Wichtiger Routing-Fix

- API-Controller duerfen nicht nur ueber den `superadmin`-Brand importiert werden.
- In `config/routes.yaml` wurde deshalb `src/Controller/Api/` separat ohne Brand-Condition importiert.
- Der allgemeine `controllers`-Import schliesst `src/Controller/Api/` aus und bleibt weiter auf `superadmin` begrenzt.
- Ohne diesen Fix faellt `/api/ecommerce/*` auf Website-Hosts in den React-SPA-Catch-all und antwortet mit `text/html` statt JSON.
- Erwarteter Test ohne Token:
  - `GET /api/ecommerce/products?limit=1`
  - Status `401`
  - Content-Type `application/json`
  - Body: `{"message":"Bearer-Token fehlt."}`

## Neues Produkt-Flag fuer API-Ausgabe

- `ShopProduct` hat ein eigenes API-Flag bekommen:
  - Property: `apiActive`
  - Getter: `isApiActive()`
  - Setter: `setApiActive(bool $apiActive)`
  - DB-Spalte: `api_active`
- Migration:
  - `migrations/Version20260717172000.php`
  - SQL: `ALTER TABLE tx_tuningtoolshop_domain_model_product ADD api_active TINYINT(1) NOT NULL DEFAULT 0`
- EasyAdmin:
  - `ShopProductCrudController`
  - Feldlabel: `API aktiv`
  - sichtbar im Index und Formular.

## API-Sichtbarkeitsregel

Ein Produkt wird nur ueber die Ecommerce-API ausgegeben, wenn alle Bedingungen erfuellt sind:

- `deleted = false`
- `hidden = false`
- `isActive = true`
- `apiActive = true`
- `sysLanguageUid = 0`
- `starttime = 0` oder Startzeit liegt in der Vergangenheit
- `endtime = 0` oder Endzeit liegt in der Zukunft

Related Products in Produktdetails werden ebenfalls nur ausgegeben, wenn sie `apiActive = true` haben.

## Hinweise fuer Uebertragung

- Migration im Schwesterportal ausfuehren, bevor die API produktiv genutzt wird:
  - `php bin/console doctrine:migrations:migrate`
- Bestehende Produkte sind nach der Migration standardmaessig **nicht** API-aktiv.
- Produkte muessen im Admin einzeln ueber `API aktiv` fuer die API freigeschaltet werden.
- Doku fuer Integrationen liegt in `docs/ecommerce-api.md`.
