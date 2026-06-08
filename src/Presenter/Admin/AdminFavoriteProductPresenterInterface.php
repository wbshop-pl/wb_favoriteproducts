<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Presenter\Admin;

use WbShop\WbFavoriteProducts\DTO\FavoriteProduct;

interface AdminFavoriteProductPresenterInterface
{
    public function present(FavoriteProduct $favoriteProduct, \Language $language): array;
}
