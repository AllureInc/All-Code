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
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Class massDisapprove
 */
class Deny extends \Webkul\Marketplace\Controller\Adminhtml\Seller\Deny
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $allStores = $this->_storeManager->getStores();
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $data['seller_id']);
        foreach ($collection as $item) {
            $item->setIsSeller(0);
            $item->setBecomeSellerRequest(0);
            $item->save();
        }

        $sellerProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $data['seller_id']
        );

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
                )->updateAttributes(
                    $productIds,
                    ['status' => $status],
                    $storeId
                );
            }
            
            $this->_objectManager->get(
                'Magento\Catalog\Model\Product\Action'
            )->updateAttributes($productIds, ['status' => $status], 0);

            $this->_productPriceIndexerProcessor->reindexList($productIds);
        }

        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );

        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail=$adminStoremail? $adminStoremail:$helper->getDefaultTransEmailId();
        /*$adminUsername = 'Admin';*/
        $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

        $seller = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        )->load($data['seller_id']);
        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $data['seller_deny_reason'];
        $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];

        $sellerStoreId = $seller->getData('store_id');

        $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Email'
        )->sendSellerDenyMail(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $sellerStoreId
        );
        $this->_eventManager->dispatch(
            'mp_deny_seller',
            ['seller' => $seller]
        );
        
        $this->messageManager->addSuccess(__('Seller has been Denied.'));

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
