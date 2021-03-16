<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Advertisement
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\Advertisement\Controller\Block;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class CartPreview extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_url;
    protected $_session;

    protected $formKey;   
    protected $cart;
    protected $product;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        PageFactory $resultPageFactory
    ) {
        $this->_session = $session;
        $this->_url = $url;
        $this->_resultPageFactory = $resultPageFactory;

        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        parent::__construct($context);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productId = $this->product->getIdBySku ('mis_adv_preview_product');
        $params = array(
                'form_key' => $this->formKey->getFormKey(),
                'product' => $productId, //product Id
                'qty'   =>1 //quantity of product
            );
        //Load the product based on productID
        $_product = $this->product->load($productId);

        $this->cart->addProduct($_product, $params);
        $this->cart->save();

        $resultPage = $this->_resultPageFactory->create();
        $params = $this->getRequest()->getParams();
        $params = array_merge($params, ['is_adv_preview' => 1]);

        if(isset($params['type']) && $params['type'] == 'checkout') {
            $this->_redirect("checkout/",['_query' => $params]);
        } else {
            $this->_redirect("checkout/cart/",['_query' => $params]);
        }
        return $resultPage;
    }
}
