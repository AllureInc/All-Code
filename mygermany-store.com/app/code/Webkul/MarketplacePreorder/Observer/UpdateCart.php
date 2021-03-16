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
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\CollectionFactory;

class UpdateCart implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var CollectionFactory
     */
    protected $_completeCollection;

    /**
     * @param RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     * @param CollectionFactory $completeCollection
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\CartFactory $cart,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        CollectionFactory $completeCollection
    ) {
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_cart = $cart;
        $this->_preorderHelper = $preorderHelper;
        $this->_completeCollection = $completeCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $msg = 'You can not update the quantity of Complete PreOrder Product.';
        $helper = $this->_preorderHelper;
        $data = $this->_request->getParams();
        $quote = $this->_cart->create()->getQuote();
        $error = false;
        foreach ($quote->getAllItems() as $quoteItem) {
            $itemId = $quoteItem->getId();
            $collection = $this->_completeCollection->create();
            $field = 'quote_item_id';
            $item = $helper->getPreorderCompleteData($field, $itemId, 'eq');
            if ($item) {
                $qty = $item['qty'];
                $finalQty = $quoteItem->getQty();
                if ($finalQty != $qty) {
                    $quoteItem->setQty($qty);
                    $error = true;
                }
            }
        }
        if ($error) {
            $this->_messageManager->addNotice(__($msg));
            $quote->save();
        }
    }
}
