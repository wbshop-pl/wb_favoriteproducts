{**
 * "Favorite products" link on the customer My-account page (displayCustomerAccount).
 * Uses the classic theme's account-link grid classes for native integration, plus
 * module-scoped classes + inline SVG so it stays theme-independent.
 *}
<a
    class="col-lg-4 col-md-6 col-sm-6 wb-fav-account-link"
    id="wb-favoriteproducts-account-link"
    href="{$favoritePageUrl}"
>
    <span class="link-item wb-fav-account-link__inner">
        <span class="wb-fav-account-link__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </span>
        {l s='Favorite products' d='Modules.Wbfavoriteproducts.Front'}
        {if $favoriteProductsCount > 0}
            <span class="wb-fav-account-link__count">({$favoriteProductsCount})</span>
        {/if}
    </span>
</a>
