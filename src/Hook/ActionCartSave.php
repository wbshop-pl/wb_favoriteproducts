<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

use WbShop\WbFavoriteProducts\Cache\TemplateCache;
use WbShop\WbFavoriteProducts\Services\FavoriteProductService;

class ActionCartSave extends AbstractHook
{
    private TemplateCache $templateCache;

    public function __construct(
        \Wb_favoriteproducts $module,
        \Context $context,
        FavoriteProductService $favoriteProductService,
        TemplateCache $templateCache
    ) {
        parent::__construct($module, $context, $favoriteProductService);
        $this->templateCache = $templateCache;
    }

    public function execute(array $params): void
    {
        if (empty($this->context->cart->id)) {
            return;
        }

        $this->templateCache->clearCartTemplateCache();
    }
}
