<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;

class AssignToCate extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
        /**
         * @var ProductRepositoryInterface
         */
    protected $_productRepository;
    
        /**
         * @var ProductMapFactory
         */
    protected $productMapRepository;
    
        /**
         * @param Context                          $context
         * @param \Magento\Customer\Model\Session  $customerSession
         * @param ProductRepositoryInterface       $productRepository
         * @param ProductMapFactory                $productMapFactory
         */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        ProductMapRepositoryInterface $productMapRepository
    ) {
        $this->customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->productMapRepository = $productMapRepository;
        parent::__construct($context);
    }
    
        /**
         *  mass product assign to category
         * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
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
            $this->messageManager->addError(__('Invalid request.'));
        }
    
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mpamazonconnect/product/unassigned'));
    }
}
