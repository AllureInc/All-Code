<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Product;

class Profiler extends \Magento\Framework\View\Element\Template
{
    private $sellerId;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Backend\Block\Widget\Context  $context
     * @param \Mangoit\RakutenConnector\Helper\Data $helperData
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Mangoit\RakutenConnector\Helper\Data $helperData,
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
