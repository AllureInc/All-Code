<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

/**
 * Interface ConfigTypeInterface
 * @package Plenty\Core\App\Config
 */
interface ConfigTypeInterface
{
    /**
     * @param string $path
     * @return mixed
     */
    public function get($path = '');

    /**
     * @return void
     */
    public function clean();
}
