<?php


namespace Mangoit\Advertisement\Block\Adminhtml;

class Pricing extends \Webkul\MpAdvertisementManager\Block\Adminhtml\Pricing
{
    /**
     * @var string
     */
    protected $_template = 'Mangoit_Advertisement::pricing.phtml';
     
    /*public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        array $data = []
    ) {
    
        $this->_adsHelper = $adsHelper;
        parent::__construct($context, $data);
    }*/

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
}
