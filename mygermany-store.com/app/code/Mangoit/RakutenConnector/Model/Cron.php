<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Magento\Framework\App\Action\Context;
use Mangoit\RakutenConnector\Helper\ProductOnRakuten as ProductToRakutenHelper;
use Magento\Framework\App\Action\Action;
use Mangoit\RakutenConnector\Model\Accounts;
use Mangoit\RakutenConnector\Helper\ManageOrderRawData;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

/**
 * custom cron actions
 */
class Cron
{
    /*
    contain Rakuten Client object
    */
    public $rktnClient;

    /*
    \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     *
     * @param Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param ProductToRakutenHelper $productToRakutenHelper
     * @param Accounts $accounts
     * @param ManageOrderRawData $manageOrderRawData
     * @param \Mangoit\RakutenConnector\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param SessionManager $coreSession
     * @param \Mangoit\RakutenConnector\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToRakutenHelper $productToRakutenHelper,
        Accounts $accounts,
        ManageOrderRawData $manageOrderRawData,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        SessionManager $coreSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mangoit\RakutenConnector\Helper\Data $helper
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productToRakuten = $productToRakutenHelper;
        $this->accounts = $accounts;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->coreSession = $coreSession;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
    }

    /**
     * cron exection code
     *
     * @return void
     */
    public function orderSyncFromRakuten()
    {
        $this->logger->info('============== cron exection started Now =================== ');
        $this->coreSession->setData('rktn_cron', 'start');
        $result = [];
        try {
            $collection = $this->accounts->getCollection();
            foreach ($collection as $account) {
                $this->rktnClient = $this->helper->getRktnClient($account->getId());
                // check feed status
                // $productMapColl = $this->productMapRepo
                //                 ->getByAccountId($account->getSellerId());
                // $productMapColl->addFieldToFilter('export_status', 0);
                // $feedIds = $this->getFeedIds($productMapColl);
                // $this->logger->info(' feed ids : '.json_encode($feedIds));
                // if (!empty($feedIds)) {
                //     $this->productToRakuten->checkProductFeedStatus($feedIds);
                // }
                
                // end check feed status
                // check amazon product by product api
                // $productMapColl = $this->productMapRepo
                //                  ->getByAccountId($account->getSellerId());
                // $productMapColl->addFieldToFilter('export_status', 0);
                // $amzProSku = $this->getProductSku($productMapColl);
                // if (!empty($amzProSku)) {
                //     $this->productToRakuten->checkProductStatusBySku($amzProSku);
                // }

                // check amazon product by product api
                // if (!$account->getSellerId()) {
                //     $this->logger->info(' Account id '.$account->getSellerId());
                //     $orderParams['recordCount'] = '40';
                //     $dt = new \DateTime();
                    
                //     $toDate = $dt->modify('-1 hour');
        
                //     $dtFrom = new \DateTime();
                //     $fromDate = $dtFrom->modify('-1 day');
    
                //     $orderLists = $this->rktnClient
                //                 ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
    
                //     $this->logger->info(' order raw data ');
                //     $this->logger->info(json_encode($orderLists));
                //     if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
                //         $amazonOrderArray = $orderLists['ListOrdersResult']['Orders']['Order'];
                //         $amzOrders = isset($amazonOrderArray[0]) ? $amazonOrderArray : [0 => $amazonOrderArray];
                //         $result = $this->manageOrderRawData->manageOrderData($amzOrders, $account->getSellerId(), true);
                //     }
                //     $this->logger->info(' these Rakuten order created ');
                //     $this->logger->info(json_encode($result));
                // }

                // Update product on Rakuten...
                $productMapColl = $this->productMapRepo
                        ->getByAccountId($account->getSellerId());
                // $productMapColl->addFieldToFilter('export_status', 0);

                $mageProdIds = $this->getProductIds($productMapColl);

                $dt = new \DateTime();
                $toDate = $dt->modify('-1 hour');

                $dtFrom = new \DateTime();
                $fromDate = $dtFrom->modify('-3 hour');

                $updatedIds = $this->productFactory->create()->getCollection() 
                                ->addFieldToFilter('entity_id', ['in' => $mageProdIds])
                                ->addFieldToFilter('updated_at', ['from' => $fromDate, 'to' => $toDate]);

                $idsToUpdate = $this->indexArrayByElement($mageProdIds,array_column($updatedIds->getData(), 'entity_id'));
                $this->logger->info(' these Rakuten products to be updated ');
                $this->logger->info(json_encode($idsToUpdate));
                if (!empty($idsToUpdate)) {
                    // $this->productToRakuten->checkProductStatusBySku($amzProSku);
                    $this->productToRakuten->updateProductsOnRktnByMageId($idsToUpdate);
                }
                if (!$account->getSellerId()) {
                    $this->logger->info(' Account id '.$account->getSellerId());
                    $orderParams['recordCount'] = '40';
                    $dt = new \DateTime();
                    
                    $toDate = $dt->modify('-1 hour');
        
                    $dtFrom = new \DateTime();
                    $fromDate = $dtFrom->modify('-1 day');

                    $orderLists = $this->rktnClient
                                ->listOrders($fromDate, $toDate, $orderParams['recordCount']);

                    $this->logger->info(' order raw data ');
                    $this->logger->info(json_encode($orderLists));

                    if (isset($orderLists['orders']['order'])) {
                        $orderListArr = $orderLists['orders']['order'];
                        $rktnOrders = isset($orderListArr[0]) ? $orderListArr : [$orderListArr];

                        if (!empty($rktnOrders)) {
                            $result = $this->manageOrderRawData->manageOrderData(
                                    $rktnOrders,
                                    $account->getSellerId(),
                                    true
                                );
                        }
                    }
                    $this->logger->info(' these Rakuten order created ');
                    $this->logger->info(json_encode($result));
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Model Cron orderSyncFromRakuten : '.$e->getMessage());
        }
        $this->coreSession->setData('rktn_cron', '');
        $this->logger->info('====================== cron exection finished================= ');
    }

    public function indexArrayByElement($array, $element)
    {
        $arrayReindexed = [];
        array_walk(
            $array,
            function ($item, $key) use (&$arrayReindexed, $element) {
                if(in_array($item, $element)) {
                    $arrayReindexed[$key] = $item;
                }
            }
        );
        return $arrayReindexed;
    }

    /**
     * get ids
     */
    public function getProductIds($productMapColl)
    {
        $mageProdIds = [];
        foreach ($productMapColl as $mappedProduct) {
            $mageProdIds[$mappedProduct->getRakutenProId()] = $mappedProduct->getMagentoProId();
        }
        return array_unique($mageProdIds);
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
