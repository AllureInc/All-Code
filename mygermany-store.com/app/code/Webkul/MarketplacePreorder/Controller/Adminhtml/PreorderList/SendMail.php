<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
    
namespace Webkul\MarketplacePreorder\Controller\Adminhtml\PreorderList;

use Magento\Framework\Controller\ResultFactory;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MarketplacePreorder\Api\PreorderItemsManagementInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;

class SendMail extends \Magento\Backend\App\Action
{
        /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** @var PreorderSellerInterfaceFactory  */
    protected $_preorderItemsFactory;

    /**
     * @var PreorderSellerManagementInterface
     */
    protected $sellerManagement;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var PreorderItemsCollection
     */
    protected $_preorderItemCollection;

    /**
     * @var ItemsRepository
     */
    protected $_itemsRepository;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Email
     */
    protected $_emailHelper;

    /**
     * @var array
     */
    protected $_notInStockProducts = [];
    /**
     * @var Filter
     */
    protected $_filter;
    /**
     *
     * @param \Magento\Backend\App\Action\Context      $context
     * @param PreorderItemsCollection                  $preorderItemCollection
     * @param PreorderItemsInterfaceFactory            $preorderItemsFactory
     * @param ItemsRepository                          $itemsRepository
     * @param DataObjectHelper                         $dataObjectHelper
     * @param \Magento\Customer\Model\Session          $customerSession
     * @param \Webkul\MarketplacePreorder\Helper\Data  $preorderHelper
     * @param \Webkul\MarketplacePreorder\Helper\Email $emailHelper
     * @param \Magento\Ui\Component\MassAction\Filter  $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PreorderItemsCollection $preorderItemCollection,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        ItemsRepository $itemsRepository,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        \Webkul\MarketplacePreorder\Helper\Email $emailHelper,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        $this->_customerSession = $customerSession;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->_preorderItemCollection = $preorderItemCollection;
        $this->_itemsRepository = $itemsRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_preorderHelper = $preorderHelper;
        $this->_emailHelper = $emailHelper;
        $this->_filter = $filter;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MarketplacePreorder::index');
    }

    public function execute()
    {
        $customerEmails = [];
        $productArray = [];
        $errorProductId = [];
        $preOrderIdArray = [];
        $error = 0;
        $collection = $this->_filter->getCollection($this->_preorderItemCollection->create());
        $collection->addFieldToFilter('status', ['eq' => 0]);
        
        foreach ($collection as $item) {
            $productId = $item->getProductId();
            $product = $this->_preorderHelper->getProduct($productId);
            $stockDetails = $this->_preorderHelper->getStockDetails($productId);
            if ($stockDetails['is_in_stock'] == 1) {
                $customerEmails[] = $item->getCustomerEmail();
                $productArray[] = $productId;
                $preOrderIdArray[] = $item->getId();
            } else {
                $error = 1;
                $errorProductId[] = $item->getProductId();
            }
        }
        $errorProductId = array_unique($errorProductId);
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (count($customerEmails) && !$error) {
            $this->_emailHelper->notifyBuyers($customerEmails, $productArray, $preOrderIdArray);
            $this->messageManager->addSuccess(__('Email sent successfully to Customer(s)'));
        }
        if ($error) {
            if(count($errorProductId)){
                if(count($customerEmails))
                $this->_emailHelper->notifyBuyers($customerEmails, $productArray, $preOrderIdArray);
                $this->messageManager->addSuccess(__('Preorder Product(s) in selected order(s) is still out of stock, Email sent successfully to Customer(s), Except these products '.implode(',',$errorProductId)));
            }

        }
        return $resultRedirect->setPath('*/*/');
    }
}
