<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

/**
 * Adds a "Favorite products" link to the customer "My account" page
 * (core hook displayCustomerAccount, rendered by every theme's my-account grid).
 */
class DisplayCustomerAccount extends AbstractDisplayHook
{
    private const TEMPLATE_FILE = 'customer-account.tpl';

    protected function getTemplate(): string
    {
        return self::TEMPLATE_FILE;
    }

    protected function assignTemplateVariables(array $params)
    {
        $this->context->smarty->assign([
            'favoritePageUrl' => $this->context->link->getModuleLink($this->module->name, 'favorite'),
            'favoriteProductsCount' => count($this->favoriteProductService->getFavoriteProducts()),
        ]);
    }
}
