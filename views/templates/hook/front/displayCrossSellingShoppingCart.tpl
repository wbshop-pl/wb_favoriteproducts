{**
 * Cart "don't forget your favorites" block. The root .js-favorite-up-selling-block
 * element is swapped in place by the JS on `updatedCart` (AJAX returns this
 * template re-rendered as `content`).
 *}
<div class="js-favorite-up-selling-block">
  {if !empty($products)}
    {include file="module:wb_favoriteproducts/views/templates/front/featured-products.tpl" products=$products}
  {/if}
</div>
