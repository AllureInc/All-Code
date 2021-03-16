<?php

namespace Mangoit\VendorPayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Mangoit VendorPayments AdminCustomerSaveAfterObserver Observer.
 */
class AdminCustomerSaveAfterObserver implements ObserverInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Filesystem                                       $filesystem,
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date,
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager,
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository,
     * @param CollectionFactory                                $collectionFactory,
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param ProductCollection                                $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerid = $customer->getId();
        $postData = $observer->getRequest()->getPostValue();

        if ($this->isSeller($customerid)) {
            // list($data, $errors) = $this->validateprofiledata($observer);

            $sellerId = $customerid;

                $collectionselect = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                )->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                if ($collectionselect->getSize() == 1) {
                    foreach ($collectionselect as $verifyrow) {
                        $autoid = $verifyrow->getEntityId();
                    }

                    $collectionupdate = $this->_objectManager->get(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    )->load($autoid);

                    if (
                        isset($postData['cancel_order_chrg_in_p']) &&
                        isset($postData['cancel_order_chrg_fxd'])
                    ) {
                        if (isset($postData['cancel_order_chrg_in_p'])) {
                            $chrgData['percent'] = $postData['cancel_order_chrg_in_p'];
                        }

                        if (isset($postData['cancel_order_chrg_fxd'])) {
                            $chrgData['fixed'] = $postData['cancel_order_chrg_fxd'];
                        }
                        
                        $jsonData = json_encode($chrgData);
                    }

                   /* $chrgData['percent'] = $postData['cancel_order_chrg_in_p'];
                    $chrgData['fixed'] = $postData['cancel_order_chrg_fxd'];
                    $jsonData = json_encode($chrgData);*/

                    if ((!isset($postData['cancel_order_chrg_in_p']) || $postData['cancel_order_chrg_in_p'] =='') && (!isset($postData['cancel_order_chrg_fxd']) || $postData['cancel_order_chrg_fxd'] == '')) {
                        $jsonData = '';
                    }
                    $collectionupdate->setCancelOrderChrgData($jsonData);
                    $collectionupdate->save();
                } else {
                    if (!isset($postData['commission'])) {
                        $postData['commission'] = 0;
                    }
                    $collectioninsert = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    );

                    if ( isset($postData['cancel_order_chrg_in_p']) && isset($postData['cancel_order_chrg_fxd']) ) {
                        if (isset($postData['cancel_order_chrg_in_p'])) {
                            $chrgData['percent'] = $postData['cancel_order_chrg_in_p'];
                        }

                        if (isset($postData['cancel_order_chrg_fxd'])) {
                            $chrgData['fixed'] = $postData['cancel_order_chrg_fxd'];
                        }

                        $jsonData = json_encode($chrgData);
                        # code...
                    }

                    if (!isset($postData['cancel_order_chrg_in_p']) && !isset($postData['cancel_order_chrg_fxd'])) {
                        $jsonData = '';
                    }

                    /*$chrgData['percent'] = $postData['cancel_order_chrg_in_p'];
                    $chrgData['fixed'] = $postData['cancel_order_chrg_fxd'];
                    $jsonData = json_encode($chrgData);

                    if (!isset($postData['cancel_order_chrg_in_p']) && !isset($postData['cancel_order_chrg_fxd'])) {
                        $jsonData = '';
                    }*/
                    $collectioninsert->setCancelOrderChrgData($jsonData);
                    $collectioninsert->setSellerId($sellerId);
                    $collectioninsert->save();
                }
            }
        return $this;
    }

    public function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid)
        ->addFieldToFilter('store_id', 0);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }

    private function validateprofiledata($observer)
    {
        $errors = [];
        $data = [];
        $paramData = $observer->getRequest()->getParams();
        foreach ($paramData as $code => $value) {
            switch ($code) :
                case 'twitter_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Twitterid cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Facebookid cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
            endswitch;
        }

        return [$data, $errors];
    }
}
