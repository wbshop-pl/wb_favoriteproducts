{**
 * Self-contained favorites grid (used by the cart up-selling block).
 * Previously extended the Falcon theme's components/featured-products.tpl; now
 * renders its own theme-independent, Bootstrap 5-friendly static grid.
 *
 * @var array $products  list of presented products (ProductLazyArray)
 *}
{if !empty($products)}
  <section class="wb-fav-upsell">
    <h3 class="wb-fav-upsell__title">
      {l s='Don\'t forget your favorite products' d='Modules.Wbfavoriteproducts.Front'}
    </h3>
    <div class="wb-fav-upsell__grid">
      {foreach from=$products item=product}
        <div class="wb-fav-upsell__col">
          {include file="module:wb_favoriteproducts/views/templates/front/_partials/product.tpl" product=$product}
        </div>
      {/foreach}
    </div>
  </section>
{/if}
