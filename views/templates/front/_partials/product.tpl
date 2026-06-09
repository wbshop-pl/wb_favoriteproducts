{**
 * Self-contained favorite product card (used by the up-selling grid).
 * Previously extended the theme miniature; now renders minimal, theme-neutral
 * markup from the presented product (ProductLazyArray).
 *
 * @var array $product  presented product
 *}
<article class="wb-fav-card">
  <a href="{$product.url|escape:'html':'UTF-8'}" class="wb-fav-card__link">
    {if $product.cover}
      <img
        class="wb-fav-card__img"
        src="{$product.cover.bySize.home_default.url|escape:'html':'UTF-8'}"
        alt="{$product.name|escape:'html':'UTF-8'}"
        loading="lazy"
      >
    {/if}
    <span class="wb-fav-card__name">{$product.name|escape:'html':'UTF-8'}</span>
  </a>
  {if $product.show_price}
    <span class="wb-fav-card__price">{$product.price|escape:'html':'UTF-8'}</span>
  {/if}
</article>
