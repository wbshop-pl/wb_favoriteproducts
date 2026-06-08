<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Cache;

use WbShop\WbFavoriteProducts\Hook\DisplayCrossSellingShoppingCart;

class TemplateCache implements TemplateCacheInterface
{
    public $cacheableHooksCollection;

    public function __construct($cacheableHooksCollection)
    {
        $this->cacheableHooksCollection = $cacheableHooksCollection;
    }

    public function clearCartTemplateCache(): void
    {
        foreach ($this->cacheableHooksCollection as $hook) {
            if ($hook instanceof DisplayCrossSellingShoppingCart) {
                $hook->clearCache();
            }
        }
    }
}
