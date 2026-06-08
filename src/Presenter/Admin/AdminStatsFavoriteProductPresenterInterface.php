<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Presenter\Admin;

use WbShop\WbFavoriteProducts\DTO\StatFavoriteProduct;

interface AdminStatsFavoriteProductPresenterInterface
{
    public function present(StatFavoriteProduct $favoriteProduct, \Language $language): array;
}
