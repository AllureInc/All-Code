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

namespace Webkul\MpAdvertisementManager\Block\Adminhtml;

class Pricing extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_MpAdvertisementManager::pricing.phtml';

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

     /**
      * __construct
      *
      * @param \Magento\Backend\Block\Template\Context    $context
      * @param array                                      $data
      * @param \Webkul\MpAdvertisementManager\Helper\Data $adsHelper
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Webkul\MpAdvertisementManager\Model\PricingFactory $pricing,
        array $data = []
    ) {
        $this->_adsHelper = $adsHelper;
        $this->_pricing = $pricing;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {

        return parent::_prepareLayout();
    }

    /**
     * getBlockPositions get block positions
     */
    public function getBlockPositions()
    {
        return $this->_adsHelper->getAdsPositions();
    }

    /**
     * getBlockSettings get block setttings by position id
     */
    public function getBlockSettings($blockId)
    {
        return $this->_adsHelper->getSettingsById($blockId);
    }

    /**
     * isAllAdsBlockPriceSet function to check that all ads positions price set or not?
     *
     * @return boolean
     */
    public function isAllAdsBlockPriceSet()
    {
        $collection = $this->_pricing->create()->getCollection();
        if ($collection->getSize() == count($this->_adsHelper->getAllAdsPositions()) - 1) {
            return 1;
        }
        return 0;
    }
}
