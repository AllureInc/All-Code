<?php
namespace Mangoit\Marketplace\Controller\Checkout;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Showdeliverydays extends \Magento\Framework\App\Action\Action
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
        $previewBlock = $this->_blockFactory->createBlock('Mangoit\Marketplace\Block\Checkout\Showdeliverydays')->setTemplate('checkout/showdeliverydays.phtml')->toHtml();
        return $this->getResponse()->setBody($previewBlock);
    }
}