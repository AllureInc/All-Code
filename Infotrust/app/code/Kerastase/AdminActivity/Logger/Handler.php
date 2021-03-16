<?php
/**
 * Kerastase
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us https://Kerastase.co.uk/contacts.
 *
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
 * @copyright  Copyright (C) 2018 Kiwi Commerce Ltd (https://Kerastase.co.uk/)
 * @license    https://Kerastase.co.uk/magento2-extension-license/
 */
namespace Kerastase\AdminActivity\Logger;

/**
 * Class Handler
 * @package Kerastase\AdminActivity\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    public $loggerType = \Kerastase\AdminActivity\Logger\Logger::INFO;

    /**
     * File name
     * @var string
     */
    public $fileName = '/var/log/adminactivity.log';
}
