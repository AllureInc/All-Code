<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 * @package Plenty\Core\Model\Logger
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
    protected $fileName = '/var/log/plenty/core.log';

    public function __construct(DriverInterface $filesystem, ?string $filePath = null, ?string $fileName = null)
    {
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
