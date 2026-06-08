{**
 * Favorite toggle button for product miniatures (listings/sliders).
 * Theme-independent markup. State is driven by the data-active attribute,
 * flipped by the module JS; the two icons are shown/hidden purely via CSS.
 *}
<a
    class="wb-fav-btn"
    href="#"
    data-action="toggleFavorite"
    data-active="false"
    {if isset($product.id) && isset($product.id_product_attribute)}data-key="{$product.id}_{$product.id_product_attribute}"{/if}
    aria-label="{l s='Add to favorites' d='Modules.Wbfavoriteproducts.Front'}"
    title="{l s='Add to favorites' d='Modules.Wbfavoriteproducts.Front'}"
>
    <span class="wb-fav-btn__icon wb-fav-btn__icon--added" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
    </span>
    <span class="wb-fav-btn__icon wb-fav-btn__icon--add" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
    </span>
</a>
