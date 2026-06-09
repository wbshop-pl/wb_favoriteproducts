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
        <svg viewBox="0 0 24 24" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.9932 5.13581C9.9938 2.7984 6.65975 2.16964 4.15469 4.31001C1.64964 6.45038 1.29697 10.029 3.2642 12.5604C4.89982 14.6651 9.84977 19.1041 11.4721 20.5408C11.6536 20.7016 11.7444 20.7819 11.8502 20.8135C11.9426 20.8411 12.0437 20.8411 12.1361 20.8135C12.2419 20.7819 12.3327 20.7016 12.5142 20.5408C14.1365 19.1041 19.0865 14.6651 20.7221 12.5604C22.6893 10.029 22.3797 6.42787 19.8316 4.31001C17.2835 2.19216 13.9925 2.7984 11.9932 5.13581Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
    <span class="wb-fav-btn__icon wb-fav-btn__icon--add" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.9932 5.13581C9.9938 2.7984 6.65975 2.16964 4.15469 4.31001C1.64964 6.45038 1.29697 10.029 3.2642 12.5604C4.89982 14.6651 9.84977 19.1041 11.4721 20.5408C11.6536 20.7016 11.7444 20.7819 11.8502 20.8135C11.9426 20.8411 12.0437 20.8411 12.1361 20.8135C12.2419 20.7819 12.3327 20.7016 12.5142 20.5408C14.1365 19.1041 19.0865 14.6651 20.7221 12.5604C22.6893 10.029 22.3797 6.42787 19.8316 4.31001C17.2835 2.19216 13.9925 2.7984 11.9932 5.13581Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
</a>
