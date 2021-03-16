<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Product;

use Magento\Framework\Controller\Result\JsonFactory;
use Mangoit\RakutenConnector\Controller\Adminhtml\Product;

class GenerateReport extends Product
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Mangoit\RakutenConnector\Helper\Data $dataHelper,
        \Mangoit\RakutenConnector\Logger\Logger $logger
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $currencyCode = null;
        $resultJson = $this->_resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        $rktnClient = $this->dataHelper->getRktnClient($id);
        try {
            if ($id && $rktnClient) {
                $listingReportId = $rktnClient->RequestReport('_GET_MERCHANT_LISTINGS_ALL_DATA_');
                $inventoryReportId = $rktnClient->RequestReport('_GET_AFN_INVENTORY_DATA_');
                if ($listingReportId && $inventoryReportId) {
                    $proReportReqList = $rktnClient->GetReportRequestStatus($listingReportId);
                    $proQtyReportReqList = $rktnClient->GetReportRequestStatus($inventoryReportId);
                }
                $amazonSellerAccount = $this->dataHelper->getSellerRktnCredentials(true);
                $amazonSellerAccount->setListingReportId($proReportReqList['ReportRequestId']);
                $amazonSellerAccount->setInventoryReportId($proQtyReportReqList['ReportRequestId']);
                $currentDate = \date('Y-m-d H:i:s');
                $amazonSellerAccount->setCreatedAt($currentDate);
                $amazonSellerAccount->setId($amazonSellerAccount->getId())->save();

                $msg = __(
                    'Report id already generated till %1, Regenerate report id for latest inventory.',
                    date(
                        'M d, Y',
                        strtotime($currentDate)
                    )
                );
                $popMsg = __(
                    'Report id successfully generated till %1, 
                    Now click on "Import Product" button to import product(s).',
                    date(
                        'M d, Y',
                        strtotime($currentDate)
                    )
                );
                $response = ['data' => $msg, 'error_msg' => false, 'pop_msg' => $popMsg];
            } else {
                $response = ['error' => 'true','error_msg' => __('Rakuten Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('Product GenerateReport : '.$e->getMessage());
            $response = ['error' => 'true','error_msg' => $e->getMessage(), 'actual_error' => $e->getMessage()];
        }
        return $resultJson->setData($response);
    }
}
