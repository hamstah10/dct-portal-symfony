# AGENTS.md - TFD CRM Symfony Application

## Build, Lint & Test Commands
```bash
php bin/console --env=test           # Run test environment
composer test                         # If available
php bin/console doctrine:migrations:migrate   # Run migrations
php bin/console make:entity          # Generate entity
```

## Architecture & Structure
- **Framework**: Symfony 8.0 with Doctrine ORM, MySQL/PostgreSQL
- **Admin UI**: EasyAdminBundle with CRUD controllers
- **Key Directories**:
  - `src/Entity/` - Doctrine entities (attributes-based ORM mapping)
  - `src/Controller/Admin/` - CRUD controllers extending AbstractCrudController
  - `src/Repository/` - Database query classes
  - `src/Service/` - Business logic (API auth, data builders, etc)
  - `migrations/` - Doctrine migration files
  - `templates/` - Twig templates for EasyAdmin
- **Database**: MySQL/PostgreSQL with Doctrine migrations
- **Key Bundles**: EasyAdminBundle, UX components (icons, charts, autocomplete, live-component, datatables)

## Code Style Guidelines
- **PHP 8.4+**: Type hints required, no closing `?>` tag
- **Entities**: Use `#[ORM\Entity]`, `#[ORM\Column]` attributes for mapping; setters return `static`
- **Naming**: PascalCase classes, camelCase methods/properties, German entity/field names permitted
- **CRUD Controllers**: Extend `AbstractCrudController`, use `#[AdminCrud]` attribute, implement `configureFields()`
- **Formatting**: 4-space indentation, LF line endings, UTF-8 (per .editorconfig)
- **Collections**: Add `@var Collection<int, Entity>` phpdoc; use `ArrayCollection`, fluent add/remove methods
- **Forms**: Use EasyAdmin Field classes (`TextField`, `AssociationField`, etc); `.onlyOnIndex()`, `.hideOnForm()` for visibility
