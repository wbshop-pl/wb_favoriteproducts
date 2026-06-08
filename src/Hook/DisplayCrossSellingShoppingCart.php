<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

use WbShop\WbFavoriteProducts\DTO\FavoriteProduct;
use WbShop\WbFavoriteProducts\Presenter\Front\FrontProductProductPresenter;
use WbShop\WbFavoriteProducts\Services\FavoriteProductService;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;

class DisplayCrossSellingShoppingCart extends AbstractCacheableDisplayHook
{
    private FrontProductProductPresenter $productPresenter;

    public function __construct(
        \Wb_favoriteproducts $module,
        \Context $context,
        FavoriteProductService $favoriteProductService,
        FrontProductProductPresenter $productPresenter
    ) {
        parent::__construct($module, $context, $favoriteProductService);
        $this->productPresenter = $productPresenter;
    }

    private const TEMPLATE_FILE = 'displayCrossSellingShoppingCart.tpl';

    protected function getTemplate(): string
    {
        return self::TEMPLATE_FILE;
    }

    protected function assignTemplateVariables(array $params)
    {
        $products = $this->favoriteProductService->getFavoriteProductsForCartUpSelling($this->getExcludedProducts());

        $products = array_map(function (array $favoriteProduct) {
            return $this->presentProduct($favoriteProduct);
        }, $products);

        $this->context->smarty->assign([
            'products' => $products,
        ]);
    }

    private function getExcludedProducts(): array
    {
        $smartyCart = $this->context->smarty->getTemplateVars('cart');
        $products = $smartyCart['products'] ?? [];
        $productsCollection = [];

        foreach ($products as $product) {
            $productsCollection[] = (new FavoriteProduct())
                ->setIdProduct((int) $product['id_product'])
                ->setIdProductAttribute((int) $product['id_product_attribute']);
        }

        return $productsCollection;
    }

    private function presentProduct(array $product): ProductLazyArray
    {
        return $this->productPresenter->present($product);
    }
}
