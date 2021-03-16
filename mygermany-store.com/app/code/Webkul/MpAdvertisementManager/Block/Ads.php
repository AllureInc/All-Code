<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Block;

use Magento\Customer\Model\Session;

class Ads extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Order
     */
    protected $_adsOrderHelper;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var  \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param \Webkul\MpAdvertisementManager\Helper\Data         $adsHelper
     * @param \Webkul\MpAdvertisementManager\Helper\Order        $adsOrderHelper
     * @param Session                                            $customerSession
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Webkul\MpAdvertisementManager\Helper\Order $adsOrderHelper,
        Session $customerSession,
        array $data = []
    ) {

        $this->_adsHelper = $adsHelper;
        $this->_adsOrderHelper = $adsOrderHelper;
        $this->_customerSession = $customerSession;
        $this->_session = $context->getSession();
        parent::__construct($context, $data);
    }

    /**
     * setTemplate set template according to demo status
     *
     * @param  $template
     * @return void
     */
    /*
    public function setTemplate($template)
    {

        if($this->isAdsDemoEnabled() === 1) {

            parent::setTemplate($template);

        } elseif($this->isAdsDemoEnabled() === 0) {

            parent::setTemplate($template);

        } else {

            parent::setTemplate($template);
        }
    }
    */

    /**
     * isAdsDemoEnabled is ads demo enabled for seller
     *
     * @return boolean
     */
    public function isAdsDemoEnabled()
    {

        return $this->_adsHelper->isAdsDemoEnable();
    }

    /**
     * Render block position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_data['position'];
    }

    /**
     * getPositionLabel get position label
     *
     * @return string
     */
    public function getPositionLabel()
    {
        return $this->_adsHelper->getPositionLabel($this->_data['position']);
    }

    /**
     * get block settings.
     *
     * @return array
     */
    public function getBlockSettings()
    {
        return $this->_adsHelper->getSettingsById($this->getPosition());
    }

    /**
     * getAdsCount get ads count
     *
     * @return int
     */
    public function getAdsCount()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['sort_order'])) {
            return $setting['sort_order'];
        }

        return false;
    }

    /**
     * getValidDays get valid days
     *
     * @return int|boolean
     */
    public function getValidDays()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['valid_for'])) {
            return $setting['valid_for'];
        }

        return false;
    }

    /**
     * getWidth ad width
     *
     * @return string|boolean
     */
    public function getWidth()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['width']) && $setting['width_type'] == 'custom') {
            return $setting['width'];
        } else {
            return 'full';
        }

        return false;
    }

    /**
     * getHeight get ad height
     *
     * @return string|boolean
     */
    public function getHeight()
    {
        $setting = $this->getBlockSettings();
        if (isset($setting['height']) && $setting['height']) {
            return $setting['height'];
        }

        return '300px';
    }

    /**
     * getHeight get ad height
     *
     * @return string|boolean
     */
    public function getHeightConfig()
    {
        $height = $this->_adsHelper->getHeightConfig();
        if ($height == "") {
            return $height;
        }
        return $height."px";
    }

    /**
     * get Auto play time
     *
     * @return string|boolean
     */
    public function getAutoPlayTime()
    {
        return $this->_adsHelper->getAutoPlayTime();
    }

    /**
     * getSellerIds get seller ids
     *
     * @return array seller ids
     */
    public function getSellerIds()
    {
        return $this->_adsOrderHelper->getAllSellersIds();
    }

    /**
     * getCurrentAds get current ads
     *
     * @return array
     */
    public function getCurrentAds()
    {
        $adBlocks = $this->_adsOrderHelper->getSellerAds($this->getSellerIds(), $this->getPosition());
        $ads = [];
        foreach ($adBlocks as $adBlock) {
            $array =$this->_adsHelper->getAdBlockHtmlDetail($adBlock['id']);
            if (!empty($array)) {
                $ads[] =$this->_adsHelper->getAdBlockHtmlDetail($adBlock['id']);
            }
        }

        return $ads;
    }

    /**
     * get media url
     *
     * @return String
     */
    public function getMediaUrl()
    {
        return $this->_adsHelper->getMediaUrl();
    }

    /**
     * getSessionVal get the value set in session for random showing pop up ads
     *
     * @return int
     */
    public function getSessionVal()
    {
        return $this->_session->getWebkulMpAdvPopupAdsSessionVal();
    }

    /**
     * setSessionVal set the value in session for random showing pop up ads
     *
     * @param [int] $val
     * @return int
     */
    public function setSessionVal($val)
    {
        $this->_session->setWebkulMpAdvPopupAdsSessionVal($val);
    }

    /**
     * isModuleEnabled function
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->_adsHelper->getMouduleStatus();
    }
}
