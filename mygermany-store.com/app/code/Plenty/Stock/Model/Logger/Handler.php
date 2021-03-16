<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 * @package Plenty\Stock\Model\Logger
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $_loggerType = Logger::INFO;

    /**
     * File Name
     * @var string
     */
    protected $fileName = '/var/log/plenty/stock.log';
}
