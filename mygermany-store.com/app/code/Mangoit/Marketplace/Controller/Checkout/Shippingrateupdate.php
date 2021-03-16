<?php
namespace Mangoit\Marketplace\Controller\Checkout;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Shippingrateupdate extends \Magento\Framework\App\Action\Action
{

    protected $cart;
    protected $_blockFactory;
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->cart = $cart;
        $this->_blockFactory = $blockFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $flag = true;
        $shippingAdd = $this->cart->getQuote()->getShippingAddress();
        if ($params['CC'] == 'reset') {
            $this->_checkoutSession->unsMISCheckout();
        } else {
            if (!empty($params['CC'])) {
                $shippingAdd->setCountryId($params['CC']);
            } else {
                $flag = false;
            }
            if (!empty($params['PC']) && (is_numeric($params['PC']))) {
                $shippingAdd->setPostcode($params['PC']);
            } else {
                $flag = false;
            }
            $shippingAdd->save();

            if ($flag && (is_numeric($params['PC'])) && (strlen($params['PC'] > 3))) {
                $previewBlock = $this->_blockFactory->createBlock('Mangoit\Marketplace\Block\Checkout\Shippingmethods')->setTemplate('checkout/addShippingMethods.phtml')->toHtml();
                return $this->getResponse()->setBody($previewBlock);
            } else {
                $message = 'Fields missing!';
                return $this->getResponse()->setBody($message);
            }
        }
    }
}