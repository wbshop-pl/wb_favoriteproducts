<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Provider;

use WbShop\WbFavoriteProducts\Entity\FavoriteProduct;
use WbShop\WbFavoriteProducts\Mapper\FavoriteProductMapper;
use WbShop\WbFavoriteProducts\Repository\FavoriteProductRepository;

class CustomerFavoriteProductsProvider implements CustomerFavoriteProductsProviderInterface
{
    /*
     * @var FavoriteProductRepository $favoriteProductsRepository
     */
    private FavoriteProductRepository $favoriteProductsRepository;

    /**
     * @var FavoriteProductMapper
     */
    private FavoriteProductMapper $favoriteProductMapper;

    public function __construct(
        FavoriteProductRepository $favoriteProductsRepository,
        FavoriteProductMapper $favoriteProductMapper
    ) {
        $this->favoriteProductsRepository = $favoriteProductsRepository;
        $this->favoriteProductMapper = $favoriteProductMapper;
    }

    public function getFavoriteProductsByCustomer(\Customer $customer, \Shop $shop): array
    {
        $favoriteProducts = $this->favoriteProductsRepository->getFavoriteProductsByCustomer(
            (int) $customer->id,
            (int) $shop->id
        );

        $products = array_map(function (FavoriteProduct $favoriteProduct) {
            return $this->favoriteProductMapper->mapFavoriteProductEntityToFavoriteProductDTO($favoriteProduct);
        }, $favoriteProducts);

        return $products;
    }
}
