<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Cache;

interface TemplateCacheInterface
{
    public function clearCartTemplateCache(): void;
}
