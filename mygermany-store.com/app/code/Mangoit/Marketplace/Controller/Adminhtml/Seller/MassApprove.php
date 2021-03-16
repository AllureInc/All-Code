<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */
namespace Mangoit\Marketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Class MassApprove
 */
class MassApprove extends \Webkul\Marketplace\Controller\Adminhtml\Seller\MassApprove
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $allStores = $this->_storeManager->getStores();
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $customerModel = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        );
        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection as $item) {
            $item->setIsSeller(1);
            $item->setBecomeSellerRequest(0);
            $item->setIsProfileApproved(1);
            $item->setUpdatedAt($this->_date->gmtDate());
            $item->save();
            $sellerProduct = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection()
            ->addFieldToFilter('seller_id', $item->getSellerId());
            
            if ($sellerProduct->getSize()) {
                $productIds = $sellerProduct->getAllIds();
                $coditionArr = [];
                foreach ($productIds as $key => $id) {
                    $condition = "`mageproduct_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $sellerProduct->setProductData(
                    $coditionData,
                    ['status' => $status]
                );
                foreach ($allStores as $eachStoreId => $storeId) {
                    $this->_objectManager->get(
                        'Magento\Catalog\Model\Product\Action'
                    )->updateAttributes($productIds, ['status' => $status], $storeId);
                }

                $this->_objectManager->get(
                    'Magento\Catalog\Model\Product\Action'
                )->updateAttributes($productIds, ['status' => $status], 0);

                $this->_productPriceIndexerProcessor->reindexList($productIds);
            }

            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail=$adminStoremail? $adminStoremail:$helper->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

            $seller = $customerModel->load($item->getSellerId());

            $emailTempVariables['myvar1'] = $seller->getName();
            $emailTempVariables['myvar2'] = $this->_storeManager->getStore()->getBaseUrl().'customer/account/login';
          /*  ->getUrl(
                'customer/account/login'
            );*/
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
			$sellerStoreId = $seller->getData('store_id');
			$receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendSellerApproveMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
				$sellerStoreId
            );
            
            $this->_eventManager->dispatch(
                'mp_approve_seller',
                ['seller'=>$seller]
            );
        }

        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been approved as seller.',
                $collection->getSize()
            )
        );
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
