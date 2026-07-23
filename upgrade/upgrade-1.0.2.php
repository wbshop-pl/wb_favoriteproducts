<?php
/**
 * Upgrade to 1.0.2.
 *
 * The `actionProductFormBuilderModifier` hook was removed: it injected the
 * "Favorite products stats" panel as an extra tab on the BO product page,
 * duplicating the `displayAdminProductsExtra` ("Modules" tab) output. Unregister
 * the now-dangling hook on existing installations.
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Wb_favoriteproducts $module
 *
 * @return bool
 */
function upgrade_module_1_0_2($module)
{
    // unregisterHook() returns false when the hook is not registered; that is
    // not a failure for the upgrade, so don't let it block the version bump.
    $module->unregisterHook('actionProductFormBuilderModifier');

    return true;
}
