<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class FavoriteListQueryBuilder extends AbstractDoctrineQueryBuilder
{
    private Context $contextAdapter;
    private \Context $context;
    private Configuration $configuration;

    public function __construct(
        Connection $connection,
        $dbPrefix,
        Context $contextAdapter,
        \Context $context,
        Configuration $configuration
    ) {
        parent::__construct($connection, $dbPrefix);

        $this->contextAdapter = $contextAdapter;
        $this->context = $context;
        $this->configuration = $configuration;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('fp.id_product, COUNT(fp.id_product) as count, pl.name');

        // We need to sort it only by count
        $orderBy = $searchCriteria->getOrderBy();
        if (!empty($orderBy)) {
            $qb->orderBy($orderBy, 'DESC');
        }

        // Doctrine DBAL 3/4 (PrestaShop 9) types these strictly and rejects null,
        // so coalesce the offset to 0 and only set a max result when a limit exists.
        $qb->setFirstResult((int) $searchCriteria->getOffset());

        $limit = $searchCriteria->getLimit();
        if (null !== $limit) {
            $qb->setMaxResults((int) $limit);
        }

        return $this->applyFilters($qb, $searchCriteria);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('fp.id_product');

        $qb = $this->applyFilters($qb, $searchCriteria);

        $newQb = $this->connection->createQueryBuilder();

        $newQb->select('COUNT(*)')
            ->from(sprintf('(%s) as subquery', $qb->getSQL()));

        foreach ($qb->getParameters() as $key => $parameter) {
            $newQb->setParameter($key, $parameter);
        }

        return $newQb;
    }

    /**
     * @return QueryBuilder
     */
    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'favorite_product', 'fp');

        // Values are cast to int before concatenation (DBAL andWhere() does not bind them).
        $idLang = (int) $this->context->language->id;

        if (\Shop::isFeatureActive() && !$this->contextAdapter->isAllShopContext()) {
            $idShop = (int) $this->contextAdapter->getContextShopID();

            $qb->join('fp', $this->dbPrefix . 'product_shop', 'p', 'p.id_product = fp.id_product')
                ->join('p', $this->dbPrefix . 'product_lang', 'pl', 'pl.id_product = p.id_product')
                ->andWhere('p.id_shop = ' . $idShop)
                ->andWhere('pl.id_shop = ' . $idShop)
                ->andWhere('pl.id_lang = ' . $idLang)
                ->andWhere('p.id_product IS NOT NULL');
        } else {
            $idShop = (int) $this->configuration->get('PS_SHOP_DEFAULT');

            $qb->join('fp', $this->dbPrefix . 'product_shop', 'p', 'p.id_product = fp.id_product')
                ->join('p', $this->dbPrefix . 'product_lang', 'pl', 'pl.id_product = p.id_product')
                ->andWhere('p.id_shop = ' . $idShop)
                ->andWhere('pl.id_shop = ' . $idShop)
                ->andWhere('pl.id_lang = ' . $idLang)
                ->andWhere('p.id_product IS NOT NULL');
        }

        $qb->groupBy('fp.id_product');

        return $qb;
    }

    private function applyFilters(QueryBuilder $qb, SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $filters = $searchCriteria->getFilters();
        if (empty($filters)) {
            return $qb;
        }

        foreach ($filters as $filterName => $filterValue) {
            if ($filterName === 'date_add') {
                $qb->andWhere('fp.date_add >= :date_add')
                    ->setParameter('date_add', $filterValue->format('Y-m-d H:i:s'));
            }
        }

        return $qb;
    }
}
