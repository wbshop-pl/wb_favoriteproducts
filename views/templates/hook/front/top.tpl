{**
 * Header favorites counter. The root element keeps the .js-favorite-top-content
 * class so the JS can swap it in place after add/remove (the AJAX response
 * returns this template re-rendered as `topContent`).
 *}
<div class="wb-fav-top js-favorite-top-content">
    <a
        class="wb-fav-top__link"
        rel="nofollow"
        href="{$favoritePageUrl}"
        aria-label="{l s='My favorite products' d='Modules.Wbfavoriteproducts.Front'}"
    >
        <span class="wb-fav-top__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </span>
        {if $favoriteProductsCount > 0}
            <span class="wb-fav-top__badge">{$favoriteProductsCount}</span>
        {/if}
    </a>
</div>
