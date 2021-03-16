<?php
/**
 * @category   Mangoit
 * @package    Mangoit_Marketplace
 * @author     Mangoit Software
 */
namespace Mangoit\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;

class AssignToCate extends \Webkul\MpAmazonConnector\Controller\Product\AssignToCate
{
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost() && $data && isset($data['unassign_sync_pro_ids'])
                && isset($data['product']['category_ids'])) {
            $mpSyncProList = $this->productMapRepository->getByMageProIds($data['unassign_sync_pro_ids'], $this->customerSession->getCustomerId());
            foreach ($mpSyncProList as $mpSyncProduct) {
                $product = $this->_productRepository->getById($mpSyncProduct->getMagentoProId());
                $product->setCategoryIds($data['product']['category_ids']);
                $product->save();
                $mpSyncProduct->setAssign(1);
                $mpSyncProduct->save();
            }
            $this->messageManager->addSuccess(__('Sync product assign to category successfuly.'));
        } else {
            if (!isset($data['unassign_sync_pro_ids']) && (!isset($data['product']['category_ids']))) {
                $this->messageManager->addError(__('Please select at least one category and product.'));
            } else if (isset($data['unassign_sync_pro_ids']) && (!isset($data['product']['category_ids']))) {
                $this->messageManager->addError(__('Please select at least one category.'));
            } else if(!isset($data['unassign_sync_pro_ids']) && (isset($data['product']['category_ids']))) {
                $this->messageManager->addError(__('Please select at least one product to assign category.'));
            } else {
                $this->messageManager->addError(__('Invalid request.'));
            }
        }
    
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mpamazonconnect/product/unassigned'));
    }
}