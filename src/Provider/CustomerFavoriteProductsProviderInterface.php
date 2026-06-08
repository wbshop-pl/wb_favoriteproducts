<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Provider;

interface CustomerFavoriteProductsProviderInterface
{
    public function getFavoriteProductsByCustomer(\Customer $customer, \Shop $shop): array;
}
