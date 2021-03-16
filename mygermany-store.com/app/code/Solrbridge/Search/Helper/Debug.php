<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

/**
 * Contact base helper
 */
class Debug
{
    const ENABLE = false;
    
    static public $_debugData = array();
    
    public static function record()
    {
        $traceData = debug_backtrace();
        $_debug = $traceData[1]['class'];
        $_debug .= ':'.$traceData[1]['function'];
        $_debug .= ':'.$traceData[1]['line'];
        
        self::$_debugData[] = $_debug;
    }
    
    public static function getDebugData()
    {
        return self::$_debugData;
    }
    
    public static function isDebug()
    {
        return self::ENABLE;
    }
    
    public static function log($data, $force = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->create('\Psr\Log\LoggerInterface');
        $directoryList = $objectManager->get('Magento\Framework\Filesystem\DirectoryList');
        $logFilePath = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LOG).'/solrbridge.log';
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($logFilePath));
        if ($force || self::isDebug()) {
            if ($data instanceof Varien_Object) {
                $data = $data->getData();
            }
            if (is_array($data)) {
                $logger->info(print_r($data, true));
            } else {
                $logger->info($data);
            }
        }
    }
}
