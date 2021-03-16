<?php
/**
 * Copyright © 2015 Cor . All rights reserved.
 */
namespace Cor\Artist\Block;
use Magento\Framework\UrlFactory;
class BaseBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Cor\Artist\Helper\Data
     */
     protected $_devToolHelper;
     
     /**
     * @var \Magento\Framework\Url
     */
     protected $_urlApp;
     
     /**
     * @var \Cor\Artist\Model\Config
     */
    protected $_config;

    /**
     * @param \Cor\Artist\Block\Context $context
     * @param \Magento\Framework\UrlFactory $urlFactory
     */
    public function __construct( \Cor\Artist\Block\Context $context
    )
    {
        $this->_devToolHelper = $context->getArtistHelper();
        $this->_config = $context->getConfig();
        $this->_urlApp=$context->getUrlFactory()->create();
        parent::__construct($context);    
    }
    
    /**
     * Function for getting event details
     * @return array
     */
    public function getEventDetails()
    {
        return  $this->_devToolHelper->getEventDetails();
    }
    
    /**
     * Function for getting current url
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlApp->getCurrentUrl();
    }
    
    /**
     * Function for getting controller url for given router path
     * @param string $routePath
     * @return string
     */
    public function getControllerUrl($routePath)
    {
        
        return $this->_urlApp->getUrl($routePath);
    }
    
    /**
     * Function for getting current url
     * @param string $path
     * @return string
     */
    public function getConfigValue($path)
    {
        return $this->_config->getCurrentStoreConfigValue($path);
    }
    
    /**
     * Function canShowArtist
     * @return bool
     */
    public function canShowArtist()
    {
        $isEnabled=$this->getConfigValue('artist/module/is_enabled');
        if($isEnabled)
        {
            $allowedIps=$this->getConfigValue('artist/module/allowed_ip');
             if(is_null($allowedIps)){
                return true;
            }
            else {
                $remoteIp=$_SERVER['REMOTE_ADDR'];
                if (strpos($allowedIps,$remoteIp) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
}
