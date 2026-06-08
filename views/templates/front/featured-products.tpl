{extends file="components/featured-products.tpl"}

{block name='featured_products_title'}
    {l s='Don\'t forget your favorite products' d='Modules.Wbfavoriteproducts.Front'}
{/block}

{block name='product_miniature'}
    {include file='module:wb_favoriteproducts/views/templates/front/_partials/product.tpl' product=$product type='slider'}
{/block}
