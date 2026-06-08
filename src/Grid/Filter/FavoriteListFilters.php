<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Grid\Filter;

use WbShop\WbFavoriteProducts\Grid\Definition\Factory\FavoriteListGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class FavoriteListFilters proves default filters for favorite list grid
 */
final class FavoriteListFilters extends Filters
{
    protected $filterId = FavoriteListGridDefinitionFactory::GRID_ID;

    /**
     * {@inheritdoc}
     */
    public static function getDefaults(): array
    {
        return [
            'limit' => 10,
            'offset' => 0,
            'orderBy' => 'count',
            'sortOrder' => 'DESC',
            'filters' => [],
        ];
    }
}
