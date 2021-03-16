<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 * @package Plenty\Item\Model\Logger
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
    protected $fileName = '/var/log/plenty/item.log';

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param string|null $filePath
     * @param string|null $fileName
     */
    public function __construct(DriverInterface $filesystem, ?string $filePath = null, ?string $fileName = null)
    {
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
