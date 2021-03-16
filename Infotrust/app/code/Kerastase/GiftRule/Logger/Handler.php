<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 11:49 PM
 */

namespace Kerastase\GiftRule\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/gift_rules.log';
}
