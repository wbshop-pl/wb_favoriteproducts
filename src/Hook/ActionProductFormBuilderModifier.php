<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

use WbShop\WbFavoriteProducts\Form\Modifier\ProductFormModifier;
use WbShop\WbFavoriteProducts\Services\FavoriteProductService;

class ActionProductFormBuilderModifier extends AbstractHook
{
    /**
     * @var ProductFormModifier
     */
    private ProductFormModifier $productFormModifier;

    /**
     * @param \Wb_favoriteproducts $module
     * @param \Context $context
     * @param FavoriteProductService $favoriteProductService
     * @param ProductFormModifier $productFormModifier
     */
    public function __construct(
        \Wb_favoriteproducts $module,
        \Context $context,
        FavoriteProductService $favoriteProductService,
        ProductFormModifier $productFormModifier
    ) {
        parent::__construct($module, $context, $favoriteProductService);
        $this->productFormModifier = $productFormModifier;
    }

    public function execute(array $params): void
    {
        $productFormBuilder = $params['form_builder'];
        $productId = $params['id'];

        $this->productFormModifier->modify($productId, $productFormBuilder);
    }
}
