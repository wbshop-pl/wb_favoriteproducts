<?php

use WbShop\WbFavoriteProducts\ProductSearchProvider\FavoriteProductsSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class Wb_favoriteproductsFavoriteModuleFrontController extends ProductListingFrontController
{
    public $module;

    public function __construct()
    {
        $this->module = Module::getInstanceByName(Tools::getValue('module'));

        if (!$this->module->active) {
            Tools::redirect('index');
        }

        $this->page_name = 'module-' . $this->module->name . '-' . Dispatcher::getInstance()->getController();

        parent::__construct();

        $this->controller_type = 'modulefront';
    }

    public function getListingLabel()
    {
        return $this->module->getTranslator()->trans('Ulubione produkty', [], 'Modules.Wbfavoriteproducts.Front');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->getTranslator()->trans('My account', [], 'Shop.Theme.Customeraccount'),
            'url' => $this->context->link->getPageLink('my-account'),
        ];

        $breadcrumb['links'][] = [
            'title' => $this->getListingLabel(),
            'url' => $this->context->link->getModuleLink($this->module->name, 'favorite'),
        ];

        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'favoritePageUrl' => $this->context->link->getModuleLink($this->module->name, 'favorite'),
        ]);

        $this->doProductSearch("module:{$this->module->name}/views/templates/front/favorite.tpl");
    }

    public function setTemplate($template, $params = [], $locale = null)
    {
        if (strpos($template, 'module:') === 0) {
            $this->template = $template;
        } else {
            parent::setTemplate($template, $params, $locale);
        }
    }

    protected function getProductSearchQuery(): ProductSearchQuery
    {
        return (new ProductSearchQuery())
            ->setSortOrder(new SortOrder('product', 'date_add', 'desc'));
    }

    protected function getDefaultProductSearchProvider(): ?FavoriteProductsSearchProvider
    {
        return $this->get(FavoriteProductsSearchProvider::class);
    }

    protected function getAjaxProductSearchVariables(): array
    {
        $search = $this->getProductSearchVariables();

        $rendered_products_top = $this->render('catalog/_partials/products-top', ['listing' => $search]);

        $this->context->smarty->assign([
            'listing' => $search,
        ]);

        $rendered_products = $this->render('catalog/_partials/products', ['listing' => $search]);
        $rendered_products_bottom = $this->render('catalog/_partials/products-bottom', ['listing' => $search]);
        $rendered_notifications = $this->render('_partials/notifications', ['notifications' => $this->prepareNotifications()]);

        $data = array_merge($search, [
            'rendered_products_top' => $rendered_products_top,
            'rendered_products' => $rendered_products,
            'rendered_products_bottom' => $rendered_products_bottom,
            'rendered_notifications' => $rendered_notifications,
        ]);

        if (!empty($data['products']) && is_array($data['products'])) {
            $data['products'] = $this->prepareProductArrayForAjaxReturn($data['products']);
        }

        return $data;
    }
}
