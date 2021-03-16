<?php
namespace Mangoit\Marketplace\Controller\Checkout;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Updateshippingmethod extends \Magento\Framework\App\Action\Action
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/checkout_info.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('=== Updateshippingmethod ===');
        $params = $this->getRequest()->getParams();

        $logger->info('## params: '.json_encode($params));
        
        $sccShippingCost = urldecode($params['code']);
        $cartData = $this->_checkoutSession->getQuote()->getId();
        $checkoutSession = $this->_checkoutSession->getQuote();
        $vtmgCost = (isset($params['vtmg']) ? urldecode($params['vtmg']) : 0);

        $logger->info('## vtmgCost: '.$vtmgCost);

        if (!empty($params)) {
            $this->_checkoutSession->setMISCheckout($sccShippingCost);

            $explodedParams = explode('_', $sccShippingCost);
            // $method->setMethodTitle($this->getConfigData('name'));
            $amount = preg_replace( '/[^0-9,"."]/', '', $explodedParams[1] );

            $this->_checkoutSession->setSccShippingCost($amount);
            $checkoutSession->setSccCost($amount);
        }
        if ($vtmgCost > 0 ) {
            $this->_checkoutSession->setVtmgShippingCost($vtmgCost);
            $checkoutSession->setVendorToMygermanyCost($vtmgCost);
        }

        $logger->info('## checkout-method: '.json_encode(get_class_methods($this->_checkoutSession)));
        $logger->info('## checkout-method data: '.json_encode($this->_checkoutSession->getData()));

        $logger->info('');

        $logger->info('## checkout-method getQuote : '.json_encode(get_class_methods($this->_checkoutSession->getQuote())));
        $logger->info('## checkout-method getQuote Data: '.json_encode($this->_checkoutSession->getQuote()->getData()));
        


        $checkoutSession->save();

        exit;
    }
}