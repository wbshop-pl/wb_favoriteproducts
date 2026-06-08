<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Provider;

interface DateFiltersProviderInterface
{
    /**
     * @return array
     */
    public function getDateFilters(): array;
}
