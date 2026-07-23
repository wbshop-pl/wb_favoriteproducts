<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    throw new \Exception('You must run "composer install --no-dev" command in module directory');
}

use WbShop\WbFavoriteProducts\Hook\HookInterface;
use WbShop\WbFavoriteProducts\Installer\ModuleInstaller;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Wb_favoriteproducts extends Module
{
    public $multistoreCompatibility = self::MULTISTORE_COMPATIBILITY_YES;

    public function __construct()
    {
        $this->name = 'wb_favoriteproducts';

        $this->author = 'WBShop.pl';
        $this->version = '1.0.3';
        $this->need_instance = 0;
        $this->controllers = ['favorite'];
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('WB Favorite products', [], 'Modules.Wbfavoriteproducts.Admin');
        $this->description = $this->trans('Lets customers mark products as favorites (as guest via cookie, or logged-in via database) and browse them on a dedicated page. Works on PrestaShop 8 and 9.', [], 'Modules.Wbfavoriteproducts.Admin');
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    private function getModuleInstaller(): ModuleInstaller
    {
        return new ModuleInstaller($this);
    }

    /**
     * @return bool
     */
    public function install(): bool
    {
        return parent::install() && $this->getModuleInstaller()->install();
    }

    /**
     * @return bool
     */
    public function uninstall(): bool
    {
        return parent::uninstall() && $this->getModuleInstaller()->uninstall();
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $serviceName
     *
     * @return T|object|null
     */
    public function getService($serviceName)
    {
        try {
            return $this->get($serviceName);
        } catch (ServiceNotFoundException $exception) {
            return null;
        }
    }

    /** @param string $methodName */
    public function __call($methodName, array $arguments)
    {
        if (str_starts_with($methodName, 'hook') && $hook = $this->getHookObject($methodName)) {
            return $hook->execute(...$arguments);
        } else {
            return null;
        }
    }

    /**
     * @param string $methodName
     *
     * @return HookInterface|null
     */
    private function getHookObject($methodName)
    {
        $serviceName = sprintf(
            'WbShop\WbFavoriteProducts\Hook\%s',
            ucwords(str_replace('hook', '', $methodName))
        );

        $hook = $this->getService($serviceName);

        return $hook instanceof HookInterface ? $hook : null;
    }

    public function getCacheId($name = null)
    {
        return parent::getCacheId($name);
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        return parent::_clearCache($template, $cache_id, $compile_id);
    }

    public function getContent(): void
    {
        \Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('wb_favoriteproducts_controller_index'));
    }
}
