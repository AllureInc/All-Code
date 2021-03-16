<?php
namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderObj;
    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $salesList;
    protected $sellerProduct;
    protected $productRepository;
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        \Webkul\Marketplace\Model\Product $sellerProduct,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->salesList = $salesList;
        $this->orderObj = $orderObj;
        $this->sellerProduct = $sellerProduct;
        $this->productRepository = $productRepository;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        $customerId = $this->customerSession->getCustomer()->getId();
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        $orderCollectionObj = $this->orderObj->getCollection();

        $salesList = $this->salesList->getCollection();
        $sellerProducts = $this->sellerProduct->getCollection()->addFieldToFilter('seller_id',$customerId);
        

        if ($customerId && (!empty($postData))) {
            $deactivate = true;
            $orderCollectionObj->addFieldToFilter('customer_id', $customerId);
            $orderData = $orderCollectionObj->getData();
            
            // Checking weather customer has pending orders or not
            // if (!empty($orderData)) {
            //     foreach ($orderData as $orderValues) {
            //         if ($orderValues['status'] != 'complete') {
            //             $deactivate = false;
            //         }
            //     }
            // }
            
            // checking weather customer is a vendor and having end user orders pending
            if ($deactivate) {
                $orderCollectionObj->clear()->getSelect()->reset('where');
                $salesFilterdRec = $salesList->addFieldToFilter('seller_id', $customerId);
                if (!empty($salesFilterdRec->getData())) {
                    foreach ($salesFilterdRec as $salesValue) {
                        //print_r($salesValue->getOrderId());
                        $orderIns = $this->orderObj->load($salesValue->getOrderId());
                        if (!empty($orderIns->getData())) {
                            if ($orderIns->getStatus() != 'complete') {
                                $deactivate = false;
                            }
                            
                        }
                    }
                }
            }
        } else {
            $deactivate = false;
        }

        if ($deactivate || (!isset($postData['deactivate_acc']))) {
            $actVal = 0;
            if (isset($postData['deactivate_acc'])) {
                $actVal = 1;
            } 

            $collectionObj = $this->_collectionFactory->create()
            ->addFieldToFilter('seller_id', $customerId);//->getFirstItem()->setAccountDeactivate($actVal)->save();
            foreach ($collectionObj as $sellerKey => $sellerValue) {
                $sellerValue->setAccountDeactivate($actVal)->save();
            }
            // $customer->setCustomAttribute('deactivated_account',$actVal); 
            // $this->_customerRepositoryInterface->save($customer);
            //Deactivating all products of a vendor
            if (!empty($sellerProducts->getData())) {
                $sellerProdctData = $sellerProducts->getData();
                foreach ($sellerProdctData as $sellerProductValue) {
                    $product = $this->productRepository->getById($sellerProductValue['mageproduct_id'], true/* edit mode */, 0/* global store*/, true/* force reload*/);
                    if (isset($postData['deactivate_acc'])) {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    } else {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    }
                    $this->productRepository->save($product);
                }
            }
            $this->messageManager
                ->addSuccessMessage(__('Account successfully deactivated!'));
        } else {
            $this->messageManager
                ->addErrorMessage(__('Please complete your all orders before deactivating account!'));
        }
         
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}