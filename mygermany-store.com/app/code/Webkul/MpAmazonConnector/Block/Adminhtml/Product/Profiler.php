<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Product;

class Profiler extends \Magento\Framework\View\Element\Template
{
    private $sellerId;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Backend\Block\Widget\Context  $context
     * @param \Webkul\MpAmazonConnector\Helper\Data $helperData
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpAmazonConnector\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperData = $helperData;
        $this->sellerId = $this->helperData->getCustomerId($this->getAccountId());
    }

    /**
     * For get total imported product count.
     * @return int
     */
    public function getTotalImported()
    {
        $collection = $this->helperData
                ->getTotalImported('product', $this->sellerId, true);
        return $collection->getSize();
    }


   /**
    * get account id
    *
    * @return int
    */
    public function getAccountId()
    {
        return $this->getRequest()->getParam('id');
    }
}
