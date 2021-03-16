<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Order;

use Webkul\MpAmazonConnector\Api\OrderMapRepositoryInterface;

class OrderSync extends \Magento\Framework\View\Element\Template
{
    private $sellerId;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        OrderMapRepositoryInterface $orderMapRepo,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderMapRepo = $orderMapRepo;
        $this->helper = $helper;
        $this->sellerId = $this->helper->getCustomerId($this->getAccountId());
    }

    /**
     * Retrieve order import url
     *
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl('*/order/import', ['id' => $this->getAccountId()]);
    }

    /**
     * Retrieve order profiler url
     *
     * @return string
     */
    public function getProfilerUrl()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->getUrl('*/order/profiler', ['id' => $this->getAccountId()]);
    }

    public function checkCurrencyRate()
    {
        $accountInfo = $this->helper->getSellerAmzCredentials(true, $this->sellerId);
        $amazonCurrency = $accountInfo->getCurrencyCode();
        $allowedCurrency = $this->helper->getAllowedCurrencies();
        if ($rate = $this->helper->getCurrencyRate($amazonCurrency)) {
            return $rate;
        } else {
            return false;
        }
    }

    /**
     * get record of temp table
     *
     * @return bool
     */
    public function getTempCount()
    {
        $collection = $this->helper->getTotalImported('order', $this->sellerId, true);
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
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
