# CLAUDE.md — wb_favoriteproducts

Guidance for working on this module. Read this first; deeper detail lives in `docs/`
(note: `docs/` is intentionally **not** committed to git — it is local working memory).

## What this module is

`wb_favoriteproducts` is a PrestaShop **8 and 9** "favorite products" (wishlist) module
by **WBShop.pl**. Customers mark products as favorites; guests are stored in a cookie,
logged-in customers in the database. There is a dedicated favorites listing page, a cart
up-selling block, product-page / miniature buttons, a header counter, plus a back-office
list and per-product / per-customer statistics.

It was **forked from** the open-source `is_favoriteproducts` module by Igor Stępień
(Oksydan), which was written specifically for the **Falcon theme**. The front-end has since
been **decoupled from the theme**: the module ships its own CSS/JS, inline SVG icons, a
self-contained Bootstrap 5-friendly markup, and a vanilla-JS (`fetch`) client that only
*optionally* uses PrestaShop's core `prestashop` event hub (feature-detected). See
`docs/theme-coupling.md` for what was changed and the few remaining notes.

## Identity / naming (all renamed from the upstream)

| Aspect | Value |
|---|---|
| Module name (folder + `$this->name`) | `wb_favoriteproducts` |
| Main class | `Wb_favoriteproducts` (in `wb_favoriteproducts.php`) |
| PHP namespace (PSR-4 → `src/`) | `WbShop\WbFavoriteProducts\` |
| DI service id prefix / tags | `wbshop.wb_favoriteproducts.*` |
| Translation domains | `Modules.Wbfavoriteproducts.{Admin,Front}` |
| FO controllers | `Wb_favoriteproductsFavoriteModuleFrontController`, `Wb_favoriteproductsAjaxModuleFrontController` |
| Admin route | `wb_favoriteproducts_controller_index` |
| Composer package | `wbshop/wb_favoriteproducts` |
| Author | `WBShop.pl` |
| Version | `1.0.0` (reset for the WB baseline; upstream upgrade script removed) |

When adding code, keep these consistent. The hook dispatch and DI config derive class
names from these strings, so a mismatch silently breaks hooks.

## Architecture in one screen

- **`wb_favoriteproducts.php`** — thin module entry. Requires `vendor/autoload.php`
  (throws if missing). Delegates install/uninstall to `ModuleInstaller`. **Hooks are not
  methods on the module** — `__call()` intercepts any `hookXxx()` call and resolves a
  service `WbShop\WbFavoriteProducts\Hook\Xxx` from the Symfony container, then calls its
  `execute()`. So every hook is a class in `src/Hook/`.
- **Symfony DI** — `config/{common,front/services,admin/services,admin/grid,routes}.yml`.
  Front vs admin have separate service files (different bindings). Services are autowired;
  `$module`, `$context`, `$dbPrefix` are bound globally. Cacheable display hooks are tagged
  `wbshop.wb_favoriteproducts.cacheable_hook`.
- **`src/` layers**: `Hook/` (one class per hook), `Services/FavoriteProductService`
  (orchestrates everything, the central piece), `Repository/` (DB + cookie + legacy SQL),
  `DTO/`, `Entity/`, `Mapper/`, `Presenter/` (front JSON + admin), `Grid/` (BO list),
  `Form/` (product tab), `Controller/AdminFavoriteController`, `Cache/TemplateCache`,
  `ProductSearchProvider/` (powers the listing page).
- **Storage**: logged-in → table `{prefix}favorite_product`; guest → JSON cookie
  `favorite_products` (90-day, 20-item guest limit). On login, guest favorites are merged
  into the DB (`actionAuthentication`). See `docs/data-model.md`.
- **Friendly URL**: `moduleRoutes` hook maps `/favoriteproducts` → the `favorite` FO
  controller (editable in BO > Traffic & SEO when friendly URLs are on).
- **Customer account**: `displayCustomerAccount` hook adds a "Favorite products" link to
  the customer My-account page.
- **Front behaviour**: `actionFrontControllerSetMedia` registers the module's own
  `views/css/wb_favoriteproducts.css` + `views/js/wb_favoriteproducts.js` and pushes AJAX
  URLs + initial favorite list + `isFavoriteProductsListingPage` via `Media::addJsDef`. The
  AJAX controller (`controllers/front/ajax.php`) handles add/remove/refresh and returns JSON
  including re-rendered hook HTML (`topContent`). See `docs/frontend.md`.

## Commands

```bash
# PHP autoloader (REQUIRED before the module can load — it throws without vendor/autoload.php)
composer install --no-dev          # or: composer dump-autoload --no-dev

# Lint
composer exec php-cs-fixer fix     # config: .php-cs-fixer.dist.php
node --check views/js/wb_favoriteproducts.js
```

Front-end assets are plain hand-written files (`views/css/`, `views/js/`) — **no build
step**, no `_theme_dev/`. Edit them directly.

PHP CLI on this machine: `D:/wamp64/bin/php/php8.3.14/php.exe`. Quick syntax check:
`find . -name '*.php' -not -path './vendor/*' -exec <php> -l {} \;`

## Conventions & gotchas

- **vendor/ is committed** so the module installs without a composer step on the server.
  After changing anything under `src/` namespaces or adding classes, run
  `composer dump-autoload --no-dev` and commit the regenerated `vendor/`.
- **Hooks must be registered** in `ModuleInstaller::HOOKS_LIST` AND exist as a class in
  `src/Hook/` AND be public in the relevant `services.yml` (front and/or admin).
- **Display hooks** extend `AbstractDisplayHook`; they return template HTML via
  `module:wb_favoriteproducts/views/templates/hook/front/<tpl>`. Override
  `getTemplate()`, optionally `assignTemplateVariables()` / `shouldBlockBeDisplayed()`.
- **Translation domain casing**: PrestaShop derives `Modules.Wbfavoriteproducts` from the
  module name. Keep the `Wbfavoriteproducts` (single-capital) spelling exactly.
- The two `controllers/front/*.php` classes are **legacy-style** (not in `src/`, no
  namespace) because PrestaShop's FO dispatcher requires the
  `<ModuleName><Controller>ModuleFrontController` naming and file location.
- PS9 compatibility is a known work item, not yet verified end-to-end. See `docs/ps8-vs-ps9.md`.

## Where to read more (local `docs/`, not in git)

- `docs/architecture.md` — DI wiring, hook dispatch, layer responsibilities
- `docs/data-model.md` — DB schema, cookie format, DTO/Entity/Mapper, guest↔customer merge
- `docs/hooks.md` — every registered hook, its job and template
- `docs/frontend.md` — JS modules, `addJsDef` vars, AJAX endpoints, prestashop events
- `docs/theme-coupling.md` — what was decoupled from Falcon + the few remaining notes
- `docs/admin.md` — back-office grid, product tab stats, customer tab, route/controller
- `docs/ps8-vs-ps9.md` — PrestaShop 8 vs 9 compatibility notes
- `docs/rename-map.md` — exact upstream→WB identifier mapping (fork provenance)
