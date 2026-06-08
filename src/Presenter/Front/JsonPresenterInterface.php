<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Presenter\Front;

use WbShop\WbFavoriteProducts\DTO\FavoriteProduct;

interface JsonPresenterInterface
{
    public function present(FavoriteProduct $favoriteProduct): string;
}
