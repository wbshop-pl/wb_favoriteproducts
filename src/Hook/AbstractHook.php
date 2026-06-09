<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

use WbShop\WbFavoriteProducts\Services\FavoriteProductService;

abstract class AbstractHook implements HookInterface
{
    protected \Wb_favoriteproducts $module;
    protected \Context $context;
    protected FavoriteProductService $favoriteProductService;

    public function __construct(
        \Wb_favoriteproducts $module,
        \Context $context,
        FavoriteProductService $favoriteProductService
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->favoriteProductService = $favoriteProductService;
    }
}
