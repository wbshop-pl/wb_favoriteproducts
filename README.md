# wb_favoriteproducts

A standalone **favorite products / wishlist** module for **PrestaShop 8 and 9**, by
[WBShop.pl](https://wbshop.pl).

Customers mark products as favorites and browse them on a dedicated page. Guests are
stored in a cookie, logged-in customers in the database, and a guest's favorites are
merged into their account on login. The module also adds a header counter, product
buttons, a cart up-selling block, a "Favorite products" link in the customer account,
plus a back-office list with per-product and per-customer statistics.

**Theme-independent.** Ships its own CSS/JS (`views/css`, `views/js`), inline SVG icons
and self-contained, Bootstrap 5-friendly markup. No build step and no dependency on a
specific theme — works on the default *classic* theme and others. It only *optionally*
uses PrestaShop's core `prestashop` front JS event hub (feature-detected).

## Requirements

- PrestaShop **8.1+** (targets PrestaShop 8 and 9)
- PHP **8.1+**

## Installation

**From a release ZIP** (recommended): download `wb_favoriteproducts-<version>.zip` from
the [Releases](../../releases) page and install it in *Back office → Modules → Module
Manager → Upload a module*.

**From source:** clone the repo into `modules/wb_favoriteproducts`, then generate the
autoloader and install:

```bash
composer install --no-dev
php bin/console prestashop:module install wb_favoriteproducts   # or install from the BO
```

> The module requires `vendor/autoload.php`; it is committed, but if you change anything
> under `src/` run `composer dump-autoload --no-dev`.

## Showing the favorite button on products

The button is rendered through hooks, so where it appears depends on which hooks your
theme calls:

| Where | Hook | Notes |
|---|---|---|
| **Product page** | `displayProductActions` | Core PrestaShop hook — works on every theme out of the box. |
| **Product miniatures** (category / search / sliders) | `displayProductFavoriteButton` | Custom hook. The theme must call it inside its miniature template. |
| **Header counter** | `displayTop` | Shows the favorites count and links to the favorites page. |
| **Cart up-selling block** | `displayCrossSellingShoppingCart` | "Don't forget your favorites" grid on the cart. |
| **Customer account** | `displayCustomerAccount` | "Favorite products" link on the *My account* page. |

To show the heart on product **miniatures** in a theme that doesn't already call the
hook, add this to the theme's `catalog/_partials/miniatures/product.tpl`:

```smarty
{hook h='displayProductFavoriteButton' product=$product}
```

The button markup uses `data-action="toggleFavorite"` and a `data-key` of
`idProduct_idProductAttribute`; the bundled JS handles the toggle, the header counter and
toasts. Style it via the module's own classes (`wb-fav-btn`, etc.).

## The favorites page

Reachable at the friendly URL **`/favoriteproducts`** (falls back to
`/module/wb_favoriteproducts/favorite`). It is a self-contained *My account* sub-page:
account-links sidebar + intro message + product listing with pagination (no sort or
grid/list controls). Breadcrumb: *Home / My account / Favorite products*.

> The friendly URL requires the `moduleRoutes` hook to be registered (reinstall the
> module if you upgraded from a version without it) and **Friendly URLs** enabled in
> *Shop parameters → Traffic & SEO*.

## Configuration

The module has no back-office settings screen; the few tunables are constants:

- **Guest cookie lifetime** — `COOKIE_LIFETIME_DAYS` (default `90`) in
  `src/Repository/FavoriteProductCookieRepository.php`.
- **Guest favorites limit** — `FAVORITE_LIMIT_FOR_GUEST` (default `20`) in
  `src/Services/FavoriteProductService.php`. Logged-in customers have no limit.
- **Accent colour** — CSS custom property, override per theme:
  `:root { --wb-fav-color: #b71029; }` (also `--wb-fav-btn-bg`, `--wb-fav-btn-color`).

## Back office

A *Favorite products analytics* page (Symfony controller + grid) lists favorited
products with date-range filters, a per-product statistics tab on the product page, and
a customer's favorites in the customer view.

## Storage

- **Logged-in customers** → table `{prefix}favorite_product`.
- **Guests** → JSON cookie `favorite_products` (90 days). On login, guest favorites are
  merged into the account and the cookie is cleared.

## Building a release

Push a version tag matching `$this->version` in `wb_favoriteproducts.php`:

```bash
git tag v1.0.0
git push origin v1.0.0
```

The `Release` GitHub Action verifies the tag matches the module version, builds an
installable ZIP (module folder at the archive root) and publishes a GitHub Release.

## Credits & license

Based on [`is_favoriteproducts`](https://github.com/Oksydan/is_favoriteproducts) by Igor
Stępień (Oksydan), `develop` branch — originally built for the Falcon theme. This fork
has been **decoupled from Falcon into a standalone, theme-independent module** for
PrestaShop 8/9 by WBShop.pl.

Licensed under the **GNU General Public License v3.0** (GPL-3.0), the same license as the
original module. See [LICENSE.md](LICENSE.md).
