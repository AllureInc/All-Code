<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Developer;

use Mangoit\RakutenConnector\Helper\ProductOnRakuten as ProductToRakutenHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mangoit\RakutenConnector\Model\Accounts;
use Mangoit\RakutenConnector\Helper\ManageOrderRawData;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;

class CronTest extends Action
{
    protected $rktnClient;


    /*
    \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductToRakutenHelper $productToRakutenHelper,
        Accounts $accounts,
        ManageOrderRawData $manageOrderRawData,
        \Magento\Backend\Model\Session $backendSession,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductMapRepositoryInterface $productMapRepo
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->productToRakuten = $productToRakutenHelper;
        $this->accounts = $accounts;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->backendSession = $backendSession;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->productMapRepo = $productMapRepo;
    }

    public function execute()
    {
        $collection = $this->accounts->getCollection()->addFieldToFilter('entity_id', ['eq' => 1]);
        echo "<pre>";
            print_r($collection->getData());
            // print_r(get_class_methods($this->productFactory->create()->getCollection()));die;
        foreach ($collection as $account) {
            $this->rktnClient = $this->helper->getRktnClient($account->getId());
            // check feed status
            $productMapColl = $this->productMapRepo
            ->getByAccountId($account->getSellerId());
            $productMapColl->addFieldToFilter('export_status', 0);
            $feedIds = $this->getFeedIds($productMapColl);
            // if (!empty($feedIds)) {
            //     $this->productToRakuten->checkProductFeedStatus($feedIds);
            // }

            // end check feed status
            // check amazon product by product api
            $productMapColl = $this->productMapRepo
                        ->getByAccountId($account->getSellerId());
            // $productMapColl->addFieldToFilter('export_status', 0);
            // print_r($productMapColl->getData());
            // $amzProSku = $this->getProductSku($productMapColl);
            $mageProdIds = $this->getProductIds($productMapColl);

            $dt = new \DateTime();
            $toDate = $dt->modify('-1 minutes');

            $dtFrom = new \DateTime();
            $fromDate = $dtFrom->modify('-3 hour');

            $updatedIds = $this->productFactory->create()->getCollection() 
                            ->addFieldToFilter('entity_id', ['in' => $mageProdIds])
                            ->addFieldToFilter('updated_at', ['from' => $fromDate, 'to' => $toDate]);

            print_r($updatedIds->getData());
            $idsToUpdate = $this->indexArrayByElement($mageProdIds,array_column($updatedIds->getData(), 'entity_id'));
            print_r($idsToUpdate);
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
                        $result = $this->manageOrderRawData->manageOrderData($rktnOrders, $account->getSellerId(), true);
                    }
                }
                $this->logger->info(' these Rakuten order created ');
                $this->logger->info(json_encode($result));
            }

            // check amazon product by product api
            // check amazon product by product api
            // $orderParams['recordCount'] = '40';
            // $dt = new \DateTime();
            
            // $toDate = $dt->modify('-1 hour');

            // $dtFrom = new \DateTime();
            // $fromDate = $dtFrom->modify('-1 day');
            // $orderLists = $this->rktnClient
            //     ->listOrders($fromDate, $toDate, $orderParams['recordCount']);
            
            // if (isset($orderLists['ListOrdersResult']['Orders']['Order'])) {
            //     $amazonOrderArray = $orderLists['ListOrdersResult']['Orders']['Order'];
            //     $amzOrders = isset($amazonOrderArray[0]) ? $amazonOrderArray : [0 => $amazonOrderArray];
            //     $result = $this->manageOrderRawData->manageOrderData($amzOrders, $account->getId(), true);
            // }
            // $this->logger->info(' these Rakuten order created ');
            // $this->logger->info(json_encode($result));
        }
        // die;
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
