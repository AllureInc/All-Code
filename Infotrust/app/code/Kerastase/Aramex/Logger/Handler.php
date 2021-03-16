<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 11:49 PM
 */

namespace Kerastase\Aramex\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/aramex.log';
}
