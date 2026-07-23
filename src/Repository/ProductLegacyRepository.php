<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Repository;

use Doctrine\DBAL\Connection;

class ProductLegacyRepository
{
    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var string
     */
    private string $dbPrefix;

    /**
     * @var string
     */
    private string $table;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     */
    public function __construct(Connection $connection, string $dbPrefix)
    {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->table = $this->dbPrefix . 'product_shop';
    }

    public function isProductExistsInStore(int $productId, int $productIdAttribute, int $storeId): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('count(p.id_product)')
            ->from($this->table, 'p')
            ->where('p.id_product = :id_product')
            ->andWhere('p.id_shop = :id_shop')
            ->setParameter('id_product', $productId)
            ->setParameter('id_product_attribute', $productIdAttribute)
            ->setParameter('id_shop', $storeId);

        if ($productIdAttribute > 0) {
            $qb->join(
                'p',
                $this->dbPrefix . 'product_attribute_shop', 'pa',
                'pa.id_product = p.id_product AND pa.id_shop = p.id_shop AND pa.id_product_attribute = :id_product_attribute');
        }

        return (bool) $qb->execute()->fetchOne();
    }

    public function checkProductActiveAndVisible(
        int $productId,
        int $productIdAttribute,
        int $storeId
    ): bool {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select('count(p.id_product)')
            ->from($this->table, 'p')
            ->where('p.id_product = :id_product')
            ->andWhere('p.id_shop = :id_shop')
            ->andWhere('p.active = 1')
            ->andWhere('p.visibility != \'none\'')
            ->setParameter('id_product', $productId)
            ->setParameter('id_product_attribute', $productIdAttribute)
            ->setParameter('id_shop', $storeId);

        if ($productIdAttribute > 0) {
            $qb->join(
                'p',
                $this->dbPrefix . 'product_attribute_shop', 'pa',
                'pa.id_product = p.id_product AND pa.id_shop = p.id_shop AND pa.id_product_attribute = :id_product_attribute');
        }

        return (bool) $qb->execute()->fetchOne();
    }

    /**
     * Filter a list of product references down to those that still exist in the given shop.
     *
     * A reference is an associative array with `id_product` and `id_product_attribute` keys.
     * A product is considered existing when its row is present in `product_shop`; when the
     * reference carries a combination (`id_product_attribute` > 0), that combination must
     * also be present in `product_attribute_shop`. Existence is intentionally independent of
     * the active/visibility state so temporarily disabled products are not treated as deleted.
     *
     * The whole set is resolved with at most two queries regardless of how many references
     * are passed, so callers can validate large favorite lists without an N+1 problem.
     *
     * @param array<int, array{id_product: int|string, id_product_attribute: int|string}> $products
     *
     * @return array<int, array{id_product: int|string, id_product_attribute: int|string}> the subset that still exists
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function filterExistingProducts(array $products, int $storeId): array
    {
        if (empty($products)) {
            return [];
        }

        $productIds = [];
        $attributeIds = [];

        foreach ($products as $product) {
            $productIds[(int) $product['id_product']] = true;

            if ((int) $product['id_product_attribute'] > 0) {
                $attributeIds[(int) $product['id_product_attribute']] = true;
            }
        }

        $existingProducts = $this->fetchExistingProductIds(array_keys($productIds), $storeId);
        $existingCombinations = empty($attributeIds)
            ? []
            : $this->fetchExistingCombinationKeys(array_keys($attributeIds), $storeId);

        $result = [];

        foreach ($products as $product) {
            $idProduct = (int) $product['id_product'];
            $idProductAttribute = (int) $product['id_product_attribute'];

            if (!isset($existingProducts[$idProduct])) {
                continue;
            }

            if ($idProductAttribute > 0 && !isset($existingCombinations[$idProduct . '_' . $idProductAttribute])) {
                continue;
            }

            $result[] = $product;
        }

        return $result;
    }

    /**
     * @param int[] $productIds
     *
     * @return array<int, true> map keyed by existing product id
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchExistingProductIds(array $productIds, int $storeId): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('p.id_product')
            ->from($this->table, 'p')
            ->where('p.id_shop = :id_shop')
            ->andWhere('p.id_product IN (:id_products)')
            ->setParameter('id_shop', $storeId)
            ->setParameter('id_products', $productIds, Connection::PARAM_INT_ARRAY);

        $existing = [];

        foreach ($qb->execute()->fetchAllAssociative() as $row) {
            $existing[(int) $row['id_product']] = true;
        }

        return $existing;
    }

    /**
     * @param int[] $attributeIds
     *
     * @return array<string, true> map keyed by existing "idProduct_idProductAttribute"
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchExistingCombinationKeys(array $attributeIds, int $storeId): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('pas.id_product, pas.id_product_attribute')
            ->from($this->dbPrefix . 'product_attribute_shop', 'pas')
            ->where('pas.id_shop = :id_shop')
            ->andWhere('pas.id_product_attribute IN (:id_product_attributes)')
            ->setParameter('id_shop', $storeId)
            ->setParameter('id_product_attributes', $attributeIds, Connection::PARAM_INT_ARRAY);

        $existing = [];

        foreach ($qb->execute()->fetchAllAssociative() as $row) {
            $existing[(int) $row['id_product'] . '_' . (int) $row['id_product_attribute']] = true;
        }

        return $existing;
    }

    public function getProductCombinationForIdProductAttribute(
        int $idProduct,
        int $idProductAttribute,
        int $idLang
    ): array {
        $qb = $this->connection->createQueryBuilder()
            ->select('alg.name AS group_name, al.`name` AS attribute_name')
            ->from($this->dbPrefix . 'product_attribute', 'pa')
            ->leftJoin('pa', $this->dbPrefix . 'product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->leftJoin('pac', $this->dbPrefix . 'attribute', 'a', 'a.id_attribute = pac.id_attribute')
            ->leftJoin('a', $this->dbPrefix . 'attribute_lang', 'al', 'a.id_attribute = al.id_attribute')
            ->leftJoin('a', $this->dbPrefix . 'attribute_group', 'ag', 'ag.id_attribute_group = a.id_attribute_group')
            ->leftJoin('ag', $this->dbPrefix . 'attribute_group_lang', 'alg', 'ag.id_attribute_group = alg.id_attribute_group')
            ->where('pa.id_product = :id_product')
            ->andWhere('pa.id_product_attribute = :id_product_attribute')
            ->andWhere('al.id_lang = :id_lang')
            ->setParameter('id_product', $idProduct)
            ->setParameter('id_product_attribute', $idProductAttribute)
            ->setParameter('id_lang', $idLang)
            ->groupBy('pa.id_product_attribute, ag.id_attribute_group')
            ->orderBy('pa.id_product_attribute');

        return $qb->execute()->fetchAllAssociative();
    }
}
