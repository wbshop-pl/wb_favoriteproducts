{**
 * Favorite toggle button for the product page (displayProductActions, a core
 * PrestaShop hook present on every theme). Labeled variant.
 *}
<div class="wb-fav-product-action">
    <a
        class="wb-fav-btn wb-fav-btn--labeled"
        href="#"
        data-action="toggleFavorite"
        data-active="false"
        {if isset($product.id) && isset($product.id_product_attribute)}data-key="{$product.id}_{$product.id_product_attribute}"{/if}
    >
        <span class="wb-fav-btn__icon wb-fav-btn__icon--added" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </span>
        <span class="wb-fav-btn__icon wb-fav-btn__icon--add" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </span>
        <span class="wb-fav-btn__label wb-fav-btn__label--add">{l s='Add to favorites' d='Modules.Wbfavoriteproducts.Front'}</span>
        <span class="wb-fav-btn__label wb-fav-btn__label--added">{l s='In your favorites' d='Modules.Wbfavoriteproducts.Front'}</span>
    </a>
</div>
