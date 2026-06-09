{**
 * Favorites listing page (/favoriteproducts).
 *
 * Self-contained "My account" sub-page: extends the theme page layout (so header,
 * footer, breadcrumb stay native), then renders a customer-links sidebar, an intro
 * message (guest vs logged-in), the product listing and pagination only — no sort
 * controls, no grid/list switch, no facets.
 *
 * Depends only on standard PrestaShop theme partials (page.tpl,
 * catalog/_partials/miniatures/product.tpl, _partials/pagination.tpl) that every
 * classic-based theme provides.
 *}
{extends file='page.tpl'}

{block name='page_title'}
  {l s='Ulubione produkty' d='Modules.Wbfavoriteproducts.Front'}
{/block}

{block name='page_content'}
  <div class="row wb-fav-page">
    <aside class="col-12 col-lg-3 wb-fav-page__sidebar">
      <ul class="wb-fav-account-links">
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.my_account}">{l s='My account' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.history}">{l s='Order history and details' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.order_slip}">{l s='Credit slips' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.addresses}">{l s='Addresses' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.identity}">{l s='Information' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item">
          <a href="{$urls.pages.discount}">{l s='Vouchers' d='Shop.Theme.Customeraccount'}</a>
        </li>
        <li class="wb-fav-account-links__item wb-fav-account-links__item--active">
          <a href="{$favoritePageUrl}" aria-current="page">{l s='Ulubione produkty' d='Modules.Wbfavoriteproducts.Front'}</a>
        </li>
      </ul>
    </aside>

    <div class="col-12 col-lg-9 wb-fav-page__main">
      {if $customer.is_logged && !$customer.is_guest}
        <p class="wb-fav-page__intro">
          {l s='Przeglądaj swoje zapisane produkty i dodaj je do koszyka w dowolnym momencie z tego miejsca.' d='Modules.Wbfavoriteproducts.Front'}
        </p>
      {else}
        <p class="wb-fav-page__intro">
          {l s='Produkty dodane do ulubionych są przechowywane przez 90 dni. Załóż konto lub zaloguj się, aby zachować je na stałe.' d='Modules.Wbfavoriteproducts.Front'}
        </p>
      {/if}

      {if $listing.products}
        <div class="products row wb-fav-page__products">
          {foreach from=$listing.products item="product"}
            {include file='catalog/_partials/miniatures/product.tpl' product=$product}
          {/foreach}
        </div>

        {block name='pagination'}
          {include file='_partials/pagination.tpl' pagination=$listing.pagination}
        {/block}
      {else}
        <p class="wb-fav-page__empty">
          {l s='Nie masz jeszcze żadnych ulubionych produktów.' d='Modules.Wbfavoriteproducts.Front'}
        </p>
      {/if}
    </div>
  </div>
{/block}
