<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

use WbShop\WbFavoriteProducts\DTO\FavoriteProduct;
use WbShop\WbFavoriteProducts\Presenter\Front\FavoriteProductJsonPresenter;
use WbShop\WbFavoriteProducts\Services\FavoriteProductService;

class ActionFrontControllerSetMedia extends AbstractHook
{
    private FavoriteProductJsonPresenter $productPresenter;

    public function __construct(
        \Wb_favoriteproducts $module,
        \Context $context,
        FavoriteProductService $favoriteProductService,
        FavoriteProductJsonPresenter $productPresenter
    ) {
        parent::__construct($module, $context, $favoriteProductService);
        $this->productPresenter = $productPresenter;
    }

    public function execute(array $params): void
    {
        $this->context->controller->registerStylesheet(
            'wb-favoriteproducts',
            'modules/' . $this->module->name . '/views/css/wb_favoriteproducts.css',
            ['media' => 'all', 'priority' => 150]
        );

        $this->context->controller->registerJavascript(
            'wb-favoriteproducts',
            'modules/' . $this->module->name . '/views/js/wb_favoriteproducts.js',
            ['position' => 'bottom', 'priority' => 150]
        );

        \Media::addJsDef([
            'addToFavoriteAction' => $this->context->link->getModuleLink($this->module->name, 'ajax', [
                'action' => 'addFavoriteProduct',
                'ajax' => '1',
            ]),
            'removeFromFavoriteAction' => $this->context->link->getModuleLink($this->module->name, 'ajax', [
                'action' => 'removeFavoriteProduct',
                'ajax' => '1',
            ]),
            'refreshFavoriteUpSellingBlockUrl' => $this->context->link->getModuleLink($this->module->name, 'ajax', [
                'action' => 'refreshUpSellingBlock',
                'ajax' => '1',
            ]),
            'favoriteProducts' => $this->getFavoriteProductsJsonData(),
            'isFavoriteProductsListingPage' => $this->context->controller instanceof \Wb_favoriteproductsFavoriteModuleFrontController,
        ]);
    }

    private function getFavoriteProductsJsonData(): array
    {
        return array_map(function (FavoriteProduct $product) {
            return $this->productPresenter->present($product);
        }, $this->favoriteProductService->getFavoriteProducts());
    }
}
