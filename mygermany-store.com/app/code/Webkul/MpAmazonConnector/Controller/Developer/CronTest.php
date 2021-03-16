<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Developer;

use Webkul\MpAmazonConnector\Helper\ProductOnAmazon as ProductToAmazonHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MpAmazonConnector\Model\Accounts;
use Webkul\MpAmazonConnector\Helper\ManageOrderRawData;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;

class CronTest extends Action
{
    protected $amzClient;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToAmazonHelper $productToAmazonHelper,
        Accounts $accounts,
        ManageOrderRawData $manageOrderRawData,
        \Magento\Backend\Model\Session $backendSession,
        \Webkul\MpAmazonConnector\Logger\Logger $logger,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        ProductMapRepositoryInterface $productMapRepo
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->productToAmazon = $productToAmazonHelper;
        $this->accounts = $accounts;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->backendSession = $backendSession;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->productMapRepo = $productMapRepo;
    }

    public function execute()
    {
        $collection = $this->accounts->getCollection()->addFieldToFilter('entity_id', ['eq' => 4]);
        foreach ($collection as $account) {
            $this->amzClient = $this->helper->getAmzClient($account->getId());
            // check feed status
            $productMapColl = $this->productMapRepo
            ->getByAccountId($account->getSellerId());
            $productMapColl->addFieldToFilter('export_status', 0);
            $feedIds = $this->getFeedIds($productMapColl);
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
            // check amazon product by product api
            // $orderParams['recordCount'] = '40';
            // $dt = new \DateTime();
            
            // $toDate = $dt->modify('-1 hour');

            // $dtFrom = new \DateTime();
            // $fromDate = $dtFrom->modify('-1 day');
            // $orderLists = $this->amzClient
            //     ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
            
            // if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
            //     $amazonOrderArray = $orderLists['ListOrdersResult']['Orders']['Order'];
            //     $amzOrders = isset($amazonOrderArray[0]) ? $amazonOrderArray : [0 => $amazonOrderArray];
            //     $result = $this->manageOrderRawData->manageOrderData($amzOrders, $account->getId(), true);
            // }
            // $this->logger->info(' these Amazon order created ');
            // $this->logger->info(json_encode($result));
        }
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
