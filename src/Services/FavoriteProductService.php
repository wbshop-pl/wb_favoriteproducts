<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Services;

use WbShop\WbFavoriteProducts\Cache\TemplateCache;
use WbShop\WbFavoriteProducts\DTO\FavoriteProduct as FavoriteProductDTO;
use WbShop\WbFavoriteProducts\Entity\FavoriteProduct;
use WbShop\WbFavoriteProducts\Mapper\FavoriteProductMapper;
use WbShop\WbFavoriteProducts\Repository\FavoriteProductCookieRepository;
use WbShop\WbFavoriteProducts\Repository\FavoriteProductLegacyRepository;
use WbShop\WbFavoriteProducts\Repository\FavoriteProductRepository;
use WbShop\WbFavoriteProducts\Repository\ProductLegacyRepository;

class FavoriteProductService
{
    private \Context $context;
    private FavoriteProductRepository $favoriteProductsRepository;
    private FavoriteProductLegacyRepository $favoriteProductsRepositoryLegacy;
    private FavoriteProductCookieRepository $favoriteProductsCookieRepository;
    private ProductLegacyRepository $productRepository;
    private FavoriteProductMapper $favoriteProductMapper;
    private TemplateCache $templateCache;

    protected $cachedFavoriteProducts = null;

    public const FAVORITE_LIMIT_FOR_GUEST = 20;

    public function __construct(
        \Context $context,
        FavoriteProductRepository $favoriteProductsRepository,
        FavoriteProductCookieRepository $favoriteProductsCookieRepository,
        ProductLegacyRepository $productRepository,
        FavoriteProductLegacyRepository $favoriteProductsRepositoryLegacy,
        FavoriteProductMapper $favoriteProductMapper,
        TemplateCache $templateCache
    ) {
        $this->context = $context;
        $this->favoriteProductsRepository = $favoriteProductsRepository;
        $this->favoriteProductsCookieRepository = $favoriteProductsCookieRepository;
        $this->productRepository = $productRepository;
        $this->favoriteProductsRepositoryLegacy = $favoriteProductsRepositoryLegacy;
        $this->favoriteProductMapper = $favoriteProductMapper;
        $this->templateCache = $templateCache;
    }

    public function isCustomerLogged(): bool
    {
        return $this->context->customer->isLogged();
    }

    public function getFavoriteProducts(): array
    {
        if (!is_null($this->cachedFavoriteProducts)) {
            return $this->cachedFavoriteProducts;
        }

        if ($this->isCustomerLogged()) {
            $this->cachedFavoriteProducts = $this->getCustomerFavoriteProducts();
        } else {
            $this->cachedFavoriteProducts = $this->getGuestFavoriteProducts();
        }

        return $this->cachedFavoriteProducts;
    }

    /**
     * @return FavoriteProductDTO[]
     */
    private function getCustomerFavoriteProducts(): array
    {
        $shopId = (int) $this->context->shop->id;

        $favoriteProducts = $this->favoriteProductsRepository->getFavoriteProductsByCustomer(
            (int) $this->context->customer->id,
            $shopId
        );

        if (empty($favoriteProducts)) {
            return [];
        }

        $status = $this->classifyFavorites($favoriteProducts, $shopId);

        $orphaned = [];
        $visible = [];

        foreach ($favoriteProducts as $favoriteProduct) {
            $key = $this->getProductKey($favoriteProduct);

            // Product removed from the shop (or deleted straight in the database): purge it.
            if (!isset($status['existing'][$key])) {
                $orphaned[] = $favoriteProduct;

                continue;
            }

            // Existing but disabled/hidden products stay stored yet are not shown or counted.
            if (isset($status['visible'][$key])) {
                $visible[] = $favoriteProduct;
            }
        }

        if (!empty($orphaned)) {
            $this->favoriteProductsRepository->removeFavoriteProducts($orphaned);
            $this->templateCache->clearCartTemplateCache();
        }

        return array_map(function (FavoriteProduct $favoriteProduct) {
            return $this->favoriteProductMapper->mapFavoriteProductEntityToFavoriteProductDTO($favoriteProduct);
        }, $visible);
    }

    /**
     * @return FavoriteProductDTO[]
     */
    private function getGuestFavoriteProducts(): array
    {
        $shopId = (int) $this->context->shop->id;

        $favoriteProducts = $this->favoriteProductsCookieRepository->getFavoriteProducts($shopId);

        if (empty($favoriteProducts)) {
            return [];
        }

        $status = $this->classifyFavorites($favoriteProducts, $shopId);

        $existing = [];
        $visible = [];
        $hasOrphaned = false;

        foreach ($favoriteProducts as $favoriteProduct) {
            $key = $this->getProductKey($favoriteProduct);

            // Product removed from the shop: drop it from the favorites cookie.
            if (!isset($status['existing'][$key])) {
                $hasOrphaned = true;

                continue;
            }

            // Disabled/hidden products stay in the cookie (they still exist) but are not shown.
            $existing[] = $favoriteProduct;

            if (isset($status['visible'][$key])) {
                $visible[] = $favoriteProduct;
            }
        }

        // Only the dedicated favorites cookie is rewritten; cart/checkout cookies are untouched.
        if ($hasOrphaned) {
            $this->favoriteProductsCookieRepository->setFavoriteProducts($existing);
            $this->templateCache->clearCartTemplateCache();
        }

        return array_values($visible);
    }

    /**
     * Classify favorites into existing/visible key maps in a single batched lookup.
     *
     * @param array $favoriteProducts objects exposing getIdProduct()/getIdProductAttribute()
     *
     * @return array{existing: array<string, true>, visible: array<string, true>}
     */
    private function classifyFavorites(array $favoriteProducts, int $shopId): array
    {
        $pairs = [];

        foreach ($favoriteProducts as $favoriteProduct) {
            $pairs[] = [
                'id_product' => $favoriteProduct->getIdProduct(),
                'id_product_attribute' => $favoriteProduct->getIdProductAttribute(),
            ];
        }

        return $this->productRepository->classifyProducts($pairs, $shopId);
    }

    /**
     * @param FavoriteProduct|FavoriteProductDTO $favoriteProduct
     */
    private function getProductKey($favoriteProduct): string
    {
        return $favoriteProduct->getIdProduct() . '_' . $favoriteProduct->getIdProductAttribute();
    }

    public function isFavoriteLimitReached(): bool
    {
        if ($this->isCustomerLogged()) {
            return false;
        } else {
            $favoriteProducts = $this->getFavoriteProducts();

            return count($favoriteProducts) >= $this->getFavoriteLimit();
        }
    }

    public function getFavoriteLimit(): int
    {
        return self::FAVORITE_LIMIT_FOR_GUEST;
    }

    /**
     * @param FavoriteProductDTO[] $excludeProducts
     *
     * @return array products
     */
    public function getFavoriteProductsForCartUpSelling(
        array $excludeProducts = []
    ): array {
        $result = $this->getFavoriteProductForListing(
            1,
            10,
            'date_add',
            'DESC',
            $excludeProducts
        );

        return $result['items'];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $orderBy
     * @param string $orderWay
     * @param FavoriteProductDTO[] $excludeProducts
     *
     * @return array ['items' => [], 'count' => int, 'page' => int]
     */
    public function getFavoriteProductForListing(
        int $page = 1,
        int $limit = 10,
        string $orderBy = 'date_add',
        string $orderWay = 'DESC',
        array $excludeProducts = []
    ): array {
        if (!$this->isCustomerLogged()) {
            $favoriteProducts = $this->getFavoriteProducts();

            $products = [];

            foreach ($favoriteProducts as $favoriteProduct) {
                foreach ($excludeProducts as $excludeProduct) {
                    if ($excludeProduct->getIdProduct() === $favoriteProduct->getIdProduct() &&
                        $excludeProduct->getIdProductAttribute() === $favoriteProduct->getIdProductAttribute()) {
                        continue 2;
                    }
                }

                $product['date_add'] = $favoriteProduct->getDateAdd();
                $product['id_product'] = $favoriteProduct->getIdProduct();
                $product['id_product_attribute'] = $favoriteProduct->getIdProductAttribute();

                $products[] = $product;
            }

            $count = count($products);

            if ($count <= ($page - 1) * $limit) {
                $page = 1 + (int) ($count / $limit);
            }

            if ($orderBy === 'date_add') {
                if (strtoupper($orderWay) === 'DESC') {
                    usort($products, function ($a, $b) {
                        return $b['date_add'] <=> $a['date_add'];
                    });
                } else {
                    usort($products, function ($a, $b) {
                        return $a['date_add'] <=> $b['date_add'];
                    });
                }
            }

            $products = array_slice($products, ($page - 1) * $limit, $limit);

            return [
                'items' => $products,
                'count' => $count,
                'page' => $page,
            ];
        } else {
            $favoriteProducts = $this->favoriteProductsRepositoryLegacy->getFavoriteProductsForListing(
                (int) $this->context->customer->id,
                (int) $this->context->shop->id,
                $page,
                $limit,
                $orderBy,
                $orderWay,
                $excludeProducts
            );

            $count = $this->favoriteProductsRepositoryLegacy->getCountFavoriteProductsForListing(
                (int) $this->context->customer->id,
                (int) $this->context->shop->id,
                $excludeProducts
            );

            return [
                'items' => $favoriteProducts,
                'count' => $count,
                'page' => $page,
            ];
        }
    }

    public function addFavoriteProduct(FavoriteProductDTO $favoriteProduct): void
    {
        if ($this->isCustomerLogged()) {
            $favoriteProductEntity = new FavoriteProduct();
            $favoriteProductEntity->setIdProduct($favoriteProduct->getIdProduct());
            $favoriteProductEntity->setIdProductAttribute($favoriteProduct->getIdProductAttribute());
            $favoriteProductEntity->setIdCustomer($favoriteProduct->getIdCustomer());
            $favoriteProductEntity->setIdShop($favoriteProduct->getIdShop());

            $this->favoriteProductsRepository->addFavoriteProduct($favoriteProductEntity);
        } else {
            $this->favoriteProductsCookieRepository->addFavoriteProduct($favoriteProduct);
        }

        $this->templateCache->clearCartTemplateCache();

        $this->cachedFavoriteProducts = null;
    }

    public function removeFavoriteProduct(FavoriteProductDTO $favoriteProduct): void
    {
        if ($this->isCustomerLogged()) {
            $favoriteProductEntity = $this->favoriteProductsRepository->getFavoriteProductByIds(
                $favoriteProduct->getIdProduct(),
                $favoriteProduct->getIdProductAttribute(),
                $favoriteProduct->getIdCustomer(),
                $favoriteProduct->getIdShop()
            );

            if (!$favoriteProductEntity) {
                return;
            }

            $this->favoriteProductsRepository->removeFavoriteProduct($favoriteProductEntity);
        } else {
            $this->favoriteProductsCookieRepository->removeFavoriteProduct($favoriteProduct);
        }

        $this->templateCache->clearCartTemplateCache();

        $this->cachedFavoriteProducts = null;
    }

    public function isProductAlreadyInFavorites(FavoriteProductDTO $favoriteProduct): bool
    {
        if ($this->isCustomerLogged()) {
            return $this->favoriteProductsRepository->isProductAlreadyInFavorites(
                $favoriteProduct->getIdProduct(),
                $favoriteProduct->getIdProductAttribute(),
                $favoriteProduct->getIdCustomer(),
                $favoriteProduct->getIdShop()
            );
        }

        return $this->favoriteProductsCookieRepository->isProductAlreadyInFavorites($favoriteProduct);
    }

    public function productExists(int $idProduct, $idProductAttribute, $idStore): bool
    {
        return $this->productRepository->isProductExistsInStore($idProduct, $idProductAttribute, $idStore);
    }

    public function mergerGuestFavoriteProductsToCustomer(int $idCustomer, int $idShop): void
    {
        $favoriteProducts = $this->favoriteProductsCookieRepository->getFavoriteProducts($idShop);

        foreach ($favoriteProducts as $favoriteProduct) {
            $favoriteProduct->setIdCustomer($idCustomer);

            if ($this->isProductAlreadyInFavorites($favoriteProduct)) {
                continue;
            }

            $this->addFavoriteProduct($favoriteProduct);
        }

        $this->favoriteProductsCookieRepository->clearFavoriteProducts();
    }
}
