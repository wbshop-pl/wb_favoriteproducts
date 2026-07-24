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
  {l s='Favorite products' d='Modules.Wbfavoriteproducts.Front'}
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
          <a href="{$favoritePageUrl}" aria-current="page">{l s='Favorite products' d='Modules.Wbfavoriteproducts.Front'}</a>
        </li>
      </ul>
    </aside>

    <div class="col-12 col-lg-9 wb-fav-page__main">
      {if $customer.is_logged && !$customer.is_guest}
        <p class="wb-fav-page__intro">
          {l s='Browse your saved products and add them to the cart at any time from here.' d='Modules.Wbfavoriteproducts.Front'}
        </p>
      {else}
        <p class="wb-fav-page__intro">
          {l s='Products added to your favorites are kept for 90 days. Create an account or sign in to keep them permanently.' d='Modules.Wbfavoriteproducts.Front'}
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
        <p class="wb-fav-page__empty alert alert-info" role="status">
          {l s='You don\'t have any favorite products yet.' d='Modules.Wbfavoriteproducts.Front'}
        </p>
      {/if}
    </div>
  </div>
{/block}
