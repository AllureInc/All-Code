<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\AmazonMagentoConnect\Model\Accounts;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

class GenerateReportId extends Product
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context              $context
     * @param JsonFactory          $resultJsonFactory
     * @param AmazonGlobal         $amazonGlobal
     * @param ManageProductRawData $manageProductRawData
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Accounts $accounts,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accounts = $accounts;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Amazon  product import Controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $response = [];
        $msg = '';
        $accountsData = [];
        try {
            $resultJson = $this->resultJsonFactory->create();
            $accountId = $this->getRequest()->getParam('id');
            $amzClient = $this->helper->getAmzClient($accountId);
            if ($amzClient) {
                $listingReportId = $amzClient->requestReport('_GET_MERCHANT_LISTINGS_DATA_');
                $inventoryReportId = $amzClient->requestReport('_GET_AFN_INVENTORY_DATA_');
                if ($listingReportId && $inventoryReportId) {
                    $proReportReqList = $amzClient->getReportRequestStatus($listingReportId);
                    $proQtyReportReqList = $amzClient->getReportRequestStatus($inventoryReportId);
                }
                $amazonSellerAccount = $this->helper->getSellerAmzCredentials(true);
                $amazonSellerAccount->setListingReportId($proReportReqList['ReportRequestId']);
                $amazonSellerAccount->setInventoryReportId($proQtyReportReqList['ReportRequestId']);
                $amazonSellerAccount->setId($amazonSellerAccount->getId());
                $currentDate = \date('Y-m-d H:i:s');
                $amazonSellerAccount->setCreatedAt($currentDate)->save();                
                $msg = __(
                    'Report id already generated till %1, Regenerate report id for latest inventory.',
                    date(
                        'M d, Y',
                        strtotime($currentDate)
                    )
                );
                $popMsg = __(
                    'Report id successfully generated till %1, 
                    Now click on "Import Product From Amazon" button to import product(s).',
                    date(
                        'M d, Y',
                        strtotime($currentDate)
                    )
                );
                $response = ['data' => $msg, 'error_msg' => false, 'pop_msg' => $popMsg];
            } else {
                $response = ['error' => 'true','error_msg' => __('Amazon Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            $response = [
                'data' => '',
                'error_msg' => __('Something went wrong, please check log files.'),
                'actual_msg' => $e->getMessage()
            ];
        }
        return $resultJson->setData($response);
    }
}
