# wb_favoriteproducts

Favorite products module for PrestaShop 8 and 9, by [WBShop.pl](https://wbshop.pl).

Lets customers mark products as favorites (works for both guests, via cookie, and
logged-in customers, via database), browse them on a dedicated favorites page, and
gives shop admins a back-office list with per-product / per-customer statistics.

## Requirements

- PrestaShop `>= 8.1.0` (targets PrestaShop 8 and 9)
- PHP `>= 8.1`
- Composer (the module autoloads its `src/` classes via `vendor/autoload.php`)

## Installation

```bash
composer install --no-dev
```

Then install the module from the PrestaShop back office (Modules > Module Manager)
or via CLI:

```bash
php bin/console prestashop:module install wb_favoriteproducts
```

## Development

Front-end assets live in `_theme_dev/` and are built with Yarn. See `CLAUDE.md`
for an architecture overview.

## License

AFL-3.0. Derived from the open-source `is_favoriteproducts` module (AFL-3.0) and
re-worked by WBShop.pl to be theme-independent and PrestaShop 8/9 compatible.
