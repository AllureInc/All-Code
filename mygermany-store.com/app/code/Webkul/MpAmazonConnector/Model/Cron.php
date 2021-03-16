<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Magento\Framework\App\Action\Context;
use Webkul\MpAmazonConnector\Helper\ProductOnAmazon as ProductToAmazonHelper;
use Magento\Framework\App\Action\Action;
use Webkul\MpAmazonConnector\Model\Accounts;
use Webkul\MpAmazonConnector\Helper\ManageOrderRawData;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

/**
 * custom cron actions
 */
class Cron
{
    /*
    contain amazon client object
    */
    public $amzClient;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param ProductToAmazonHelper $productToAmazonHelper
     * @param Accounts $accounts
     * @param ManageOrderRawData $manageOrderRawData
     * @param \Webkul\MpAmazonConnector\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param SessionManager $coreSession
     * @param \Webkul\MpAmazonConnector\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToAmazonHelper $productToAmazonHelper,
        Accounts $accounts,
        ManageOrderRawData $manageOrderRawData,
        \Webkul\MpAmazonConnector\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        SessionManager $coreSession,
        \Webkul\MpAmazonConnector\Helper\Data $helper
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productToAmazon = $productToAmazonHelper;
        $this->accounts = $accounts;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->coreSession = $coreSession;
        $this->helper = $helper;
    }

    /**
     * cron exection code
     *
     * @return void
     */
    public function orderSyncFromAmazon()
    {
        $this->logger->info('============== cron exection started Now =================== ');
        $this->coreSession->setData('amz_cron', 'start');
        $result = [];
        try {
            $collection = $this->accounts->getCollection();
            foreach ($collection as $account) {
                $this->amzClient = $this->helper->getAmzClient($account->getId());
                // check feed status
                $productMapColl = $this->productMapRepo
                                ->getByAccountId($account->getSellerId());
                $productMapColl->addFieldToFilter('export_status', 0);
                $feedIds = $this->getFeedIds($productMapColl);
                $this->logger->info(' feed ids : '.json_encode($feedIds));
                if (!empty($feedIds)) {
                    $this->productToAmazon->checkProductFeedStatus($feedIds);
                }
                
                // end check feed status
                // check amazon product by product api
                $productMapColl = $this->productMapRepo
                                 ->getByAccountId($account->getSellerId());
                $productMapColl->addFieldToFilter('export_status', 0);
                $amzProSku = $this->getProductSku($productMapColl);
                if (!empty($amzProSku)) {
                    $this->productToAmazon->checkProductStatusBySku($amzProSku);
                }

                // check amazon product by product api
                if (!$account->getSellerId()) {
                    $this->logger->info(' Account id '.$account->getSellerId());
                    $orderParams['recordCount'] = '40';
                    $dt = new \DateTime();
                    
                    $toDate = $dt->modify('-1 hour');
        
                    $dtFrom = new \DateTime();
                    $fromDate = $dtFrom->modify('-1 day');
    
                    $orderLists = $this->amzClient
                                ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
    
                    $this->logger->info(' order raw data ');
                    $this->logger->info(json_encode($orderLists));
                    if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
                        $amazonOrderArray = $orderLists['ListOrdersResult']['Orders']['Order'];
                        $amzOrders = isset($amazonOrderArray[0]) ? $amazonOrderArray : [0 => $amazonOrderArray];
                        $result = $this->manageOrderRawData->manageOrderData($amzOrders, $account->getSellerId(), true);
                    }
                    $this->logger->info(' these Amazon order created ');
                    $this->logger->info(json_encode($result));
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Model Cron OrderSyncFromAmazon : '.$e->getMessage());
        }
        $this->coreSession->setData('amz_cron', '');
        $this->logger->info('====================== cron exection finished================= ');
    }

    /**
     * get All feed ids
     */
    public function getProductSku($productMapColl)
    {
        $amzProSku = [];
        foreach ($productMapColl as $mappedProduct) {
            $amzProSku[] = $mappedProduct->getProductSku();
        }
        return array_unique($amzProSku);
    }

    /**
     * get All feed ids
     */
    public function getFeedIds($productMapColl)
    {
        $feedArray = [];
        foreach ($productMapColl as $feed) {
            $feedArray[] = $feed->getFeedsubmissionId();
        }
        return array_unique($feedArray);
    }
}
