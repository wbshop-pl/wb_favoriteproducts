<?php

declare(strict_types=1);

namespace WbShop\WbFavoriteProducts\Hook;

interface HookInterface
{
    public function execute(array $params);
}
