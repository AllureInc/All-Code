<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Product;

class ProductSync extends \Magento\Framework\View\Element\Template
{
    private $sellerId;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->sellerId = $this->helper->getCustomerId($this->getAccountId());
    }

    /**
     * Retrieve order model instance
     *
     * @return string
     */
    public function getImportUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/import', ['id' => $id]);
    }

    /**
     * Retrieve order model instance
     *
     * @return string
     */
    public function getProfilerUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/profiler', ['id' => $id]);
    }

    /**
     * generate report id
     *
     * @return string
     */
    public function getGenerateReportUrl()
    {
        $id = $this->getAccountId();
        return $this->getUrl('*/product/generatereport', ['id' => $id]);
    }

    /**
     * check report status
     *
     * @return string
     */
    public function getReportStatus()
    {
        $accountInfo = $this->helper->getSellerRktnCredentials(true, $this->getAccountId());
        if (!empty($accountInfo->getListingReportId())) {
            return $accountInfo->getCreatedAt();
        } else {
            return false;
        }
    }

    /**
     * check currency rate
     *
     * @return int | bool
     */
    public function checkCurrencyRate()
    {
        $accountInfo = $this->helper->getSellerRktnCredentials(true, $this->getAccountId());
        $amazonCurrency = $accountInfo->getCurrencyCode();
        $allowedCurrency = $this->helper->getAllowedCurrencies();
        if (in_array($amazonCurrency, $allowedCurrency)) {
            return $this->helper->getCurrencyRate($amazonCurrency);
        } else {
            return false;
        }
    }

    /**
     * get url of exported button
     *
     * @return string
     */
    // public function getExportButtonUrl()
    // {
    //     return $this->getUrl('*/producttorakuten/updatestatusofexportedpro', ['id' => $this->getAccountId()]);
    // }

    /**
     * get record of temp table
     *
     * @return bool
     */
    public function getTempCount()
    {
        $collection = $this->helper->getTotalImported('product', $this->sellerId, true);
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get exported pending records Count
     *
     * @return bool
     */
    public function getExportedCount()
    {
        $collection = $this->helper->getExportedProColl($this->sellerId);
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
