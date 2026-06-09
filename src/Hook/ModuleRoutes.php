<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

/**
 * Registers a friendly URL for the favorites listing front controller.
 *
 * Maps `/favoriteproducts` to the module `favorite` controller instead of the
 * default `/module/wb_favoriteproducts/favorite`. The rule can be overridden per
 * shop in Back office > Shop parameters > Traffic & SEO (when friendly URLs are on).
 *
 * Does not extend AbstractHook on purpose: routing fires on every request and
 * needs no services.
 */
class ModuleRoutes implements HookInterface
{
    private const MODULE_NAME = 'wb_favoriteproducts';
    private const CONTROLLER = 'favorite';
    private const FRIENDLY_RULE = 'favoriteproducts';

    public function execute(array $params): array
    {
        return [
            'module-' . self::MODULE_NAME . '-' . self::CONTROLLER => [
                'controller' => self::CONTROLLER,
                'rule' => self::FRIENDLY_RULE,
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => self::MODULE_NAME,
                    'controller' => self::CONTROLLER,
                ],
            ],
        ];
    }
}
