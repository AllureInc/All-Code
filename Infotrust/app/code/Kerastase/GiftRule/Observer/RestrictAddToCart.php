<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
 
class RestrictAddToCart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var $quote
     */
    protected $_quote; 
 
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Kerastase\GiftRule\Helper\Data $giftruleHelper
     * @param RequestInterface $request
     * @param \Magento\Checkout\Model\Cart $quote
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Kerastase\GiftRule\Helper\Data $giftruleHelper,
        RequestInterface $request,
        \Magento\Checkout\Model\Cart $quote
    ) {
        $this->_giftruleHelper = $giftruleHelper;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
        $this->_quote = $quote;
    }
 
    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postValues = $this->_request->getPostValue();
        $quote = $this->_quote->getQuote();
        $gift_items = $this->_giftruleHelper->getGiftProductData($quote);
        $productId  = $postValues['product'];
        if (in_array($productId, $gift_items)) {
            $observer->getRequest()->setParam('product', false);
            $this->_messageManager->addError(__('This product cannot be added to your cart.'));
            
            return $this;
        }
    }
}
