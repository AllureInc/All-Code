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
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\MarketplacePreorder\Model\PreorderCompleteRepository as CompleteRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterfaceFactory;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;

class AfterAddProductToCart implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Webkul\Preorder\Model\ItemFactory
     */
    protected $_item;

    /**
     * @var \Webkul\Preorder\Model\CompleteFactory
     */
    protected $_complete;

    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var CollectionFactory
     */
    protected $_itemCollection;

     /**
     * @var CompleteRepository
     */
    protected $_completeRepository;

     /**
     * @var PreorderCompleteInterfaceFactory
     */
    protected $_preorderCompleteFactory;

     /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CollectionFactory
     */
    protected $_completeCollection;

    /**
     * @param RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Webkul\Preorder\Model\ItemFactory $item
     * @param \Webkul\Preorder\Model\CompleteFactory $complete
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     * @param CollectionFactory $itemCollection
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\CartFactory $cart,
        \Webkul\MarketplacePreorder\Model\PreorderCompleteFactory $complete,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        CompleteRepository $completeRepository,
        PreorderCompleteInterfaceFactory $preorderCompleteFactory,
        DataObjectHelper $dataObjectHelper,
        CollectionFactory $completeCollection
    ) {
        $this->_request = $request;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_cart = $cart;
        $this->_complete = $complete;
        $this->_preorderHelper = $preorderHelper;
        $this->_completeRepository = $completeRepository;
        $this->_preorderCompleteFactory = $preorderCompleteFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_completeCollection = $completeCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $cartModel = $this->_cart->create();
        $itemId = 0;
        $productId = 0;
        $cart = $cartModel->getQuote();
        foreach ($cart->getAllItems() as $item) {
            $itemId = $item->getId();
            $itemPrice = $item->getPrice();
            $productId = $item->getProductId();
        }
        $preorderCompleteProductId = $helper->getPreorderCompleteProductId();
        if ($productId == $preorderCompleteProductId) {
            if (!$this->_customerSession->isLoggedIn()) {
                $msg = 'There was some error while processing your request.';
                $this->_messageManager->addNotice(__($msg));
            }
            $customerId = (int) $this->_customerSession->getCustomerId();
            $data = $this->_request->getParams();
            
            $qty = $data['qty'];
            $orderId = $data['order_id'];
            $orderItemId = $data['item_id'];
            $preorderProductId = $data['pro_id'];
            $completeData = [
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'customer_id' => $customerId,
                'product_id' => $preorderProductId,
                'quote_item_id' => $itemId,
                'qty' => $qty,
            ];

            $collection = $this->_completeCollection->create()
                ->addFieldToFilter('order_item_id', ['eq' => $orderItemId]);

            $entityId = 0;

            foreach ($collection as $value) {
                $entityId = $value->getId();
            }
            if ($entityId) {
                $completeData['id'] = $entityId;
            }
            $completeDataObject = $this->_preorderCompleteFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $completeDataObject,
                $completeData,
                '\Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface'
            );
            try {
                $this->_completeRepository->save($completeDataObject);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
    }
}
