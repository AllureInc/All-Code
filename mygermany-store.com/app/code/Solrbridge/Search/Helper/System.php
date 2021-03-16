<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

/**
 * This class is used widely in the module to get System resources like
 * Object manager, registry, store manager,....
 */
class System
{
    public static function getObjectManager()
    {
        return self::_getObjectManager();
    }
    
    public static function getRegistry()
    {
        return self::_getRegistry();
    }
    
    public static function getEncryptor()
    {
        return self::_getObjectManager()->get('Magento\Framework\Encryption\EncryptorInterface');
    }
    
    public static function getCatalogSession()
    {
        return self::_getObjectManager()->get('Magento\Catalog\Model\Session');
    }
    
    public static function getRequest()
    {
        return self::_getObjectManager()->get('Magento\Framework\App\RequestInterface');
    }
    
    public static function getHelper()
    {
        return self::_getObjectManager()->get('Solrbridge\Search\Helper\Data');
    }
    
    private static function _getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    private static function _getRegistry()
    {
        return self::_getObjectManager()->get('Magento\Framework\Registry');
    }
}
