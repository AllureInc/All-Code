<?php
namespace Mangoit\Orderdispatch\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class Printslip extends \Magento\Framework\App\Action\Action
{
    protected $_packagingSlip;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\Orderdispatch\Helper\PackagingSlip $packagingSlip,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->orderObj = $orderObj;
        $this->_storeManager = $storeManager;
        $this->_packagingSlip = $packagingSlip;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        if ($orderId) {
            $orderState = $this->orderObj->load($orderId)->getState();
            if ($orderState == 'new' || $orderState == 'processing' || ($orderState == 'compliance_check')) {
                $pdfHtmlContent = $this->_packagingSlip->getPdfHtmlContent($orderId);
                /*$resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);*/
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order/view/',
                    ['_secure' => $this->getRequest()->isSecure(),'params' => ['id'=> $orderId]]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/dashboard/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
                
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
