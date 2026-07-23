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

    /**
     * Classify a list of product references by whether they still exist in the shop and
     * whether they are currently active and visible.
     *
     * A reference is an associative array with `id_product` and `id_product_attribute` keys.
     * A product exists when its row is present in `product_shop`; when the reference carries
     * a combination (`id_product_attribute` > 0), that combination must also be present in
     * `product_attribute_shop`. A product is "visible" when it additionally is active and its
     * visibility is not `none`. Existence and visibility are reported separately so callers can
     * delete genuinely removed products while merely keeping disabled ones hidden.
     *
     * The whole set is resolved with at most two queries regardless of list size, avoiding an
     * N+1 on `hookActionFrontControllerSetMedia`, which runs on every front page.
     *
     * @param array<int, array{id_product: int|string, id_product_attribute: int|string}> $products
     *
     * @return array{existing: array<string, true>, visible: array<string, true>} maps keyed by "idProduct_idProductAttribute"
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function classifyProducts(array $products, int $storeId): array
    {
        if (empty($products)) {
            return ['existing' => [], 'visible' => []];
        }

        $productIds = [];
        $attributeIds = [];

        foreach ($products as $product) {
            $productIds[(int) $product['id_product']] = true;

            if ((int) $product['id_product_attribute'] > 0) {
                $attributeIds[(int) $product['id_product_attribute']] = true;
            }
        }

        $productExists = [];
        $productVisible = [];

        $qb = $this->connection->createQueryBuilder()
            ->select('p.id_product, p.active, p.visibility')
            ->from($this->table, 'p')
            ->where('p.id_shop = :id_shop')
            ->andWhere('p.id_product IN (:id_products)')
            ->setParameter('id_shop', $storeId)
            ->setParameter('id_products', array_keys($productIds), Connection::PARAM_INT_ARRAY);

        foreach ($qb->execute()->fetchAllAssociative() as $row) {
            $idProduct = (int) $row['id_product'];
            $productExists[$idProduct] = true;

            if ((int) $row['active'] === 1 && $row['visibility'] !== 'none') {
                $productVisible[$idProduct] = true;
            }
        }

        $existingCombinations = empty($attributeIds)
            ? []
            : $this->fetchExistingCombinationKeys(array_keys($attributeIds), $storeId);

        $existing = [];
        $visible = [];

        foreach ($products as $product) {
            $idProduct = (int) $product['id_product'];
            $idProductAttribute = (int) $product['id_product_attribute'];
            $key = $idProduct . '_' . $idProductAttribute;

            // A missing combination means the reference is orphaned, just like a missing product.
            if ($idProductAttribute > 0 && !isset($existingCombinations[$key])) {
                continue;
            }

            if (isset($productExists[$idProduct])) {
                $existing[$key] = true;
            }

            if (isset($productVisible[$idProduct])) {
                $visible[$key] = true;
            }
        }

        return ['existing' => $existing, 'visible' => $visible];
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
