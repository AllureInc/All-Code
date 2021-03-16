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
namespace Webkul\MpAdvertisementManager\Block\Block;

use Webkul\MpAdvertisementManager\Controller\AbstractAds;

class Create extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry ;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpAdvertisementManager\Helper\Data       $adsHelper
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Config                $wysiwygConfig
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_adsHelper = $adsHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * getCurrentBlock get the current block
     *
     * @return int
     */
    public function getCurrentBlock()
    {
        return $this->_coreRegistry->registry(AbstractAds::CURRENT_BLOCK);
    }

    /**
     * getMediaUrl get the media folder url
     *
     * @return String
     */
    public function getMediaUrl()
    {
        return $this->_adsHelper->getMediaUrl();
    }
    
    /**
     * getHeightConfig get the height value for ad(s) from configuration
     *
     * @return int
     */
    public function getHeightConfig()
    {
        return $this->_adsHelper->getHeightConfig();
    }

    /**
     * getWysiwygConfig get the what you see what you get configuration
     *
     * @return String
     */
    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        return $config = json_encode($config->getData());
    }
}
