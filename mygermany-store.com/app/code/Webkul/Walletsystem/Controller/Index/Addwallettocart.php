<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/*use Magento\Catalog\Model\ProductFactory;*/
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;

class Addwallettocart extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;
    /**
     * @var ProductFactory
     */
    protected $_productFactory;
    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    protected $helper;
    /**
     * @param Context                              $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param PageFactory                          $resultPageFactory
     * @param ProductFactory                       $productFactory
     * @param cart                                 $cartModel
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Product $productFactory,
        cart $cartModel,
        \Webkul\Walletsystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_formKey = $formKey;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_productFactory = $productFactory;
        $this->_messageManager = $messageManager;
        $this->_cartModel = $cartModel;
        $this->helper = $helper;
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $price = $this->getRequest()->getParam('price');
        $baseCurrenyCode = $this->helper->getBaseCurrencyCode();
        $currencySymbol = $this->helper->getCurrencySymbol(
            $this->helper->getCurrentCurrencyCode()
        );
        $currentCurrenyCode = $this->helper->getCurrentCurrencyCode();
        $adminConfigPrice = $this->helper->getMinimumAmount();
        $finalminimumAmount = $this->helper->getwkconvertCurrency(
            $baseCurrenyCode,
            $currentCurrenyCode,
            $adminConfigPrice
        );
        if ($price < $finalminimumAmount) {
            $this->_messageManager->addNotice(
                __(
                    'You can not add less than %1 amount to your wallet.',
                    $currencySymbol.$finalminimumAmount
                )
            );
            return $resultRedirect->setPath('walletsystem/index/index');
        }
        if (array_key_exists('product', $wholedata)) {
            $params = [
                'form_key' => $this->_formKey->getFormKey(),
                'product' =>$wholedata['product'],
                'qty'   =>1,
                'price' =>$wholedata['price']
            ];
            $resultRedirect->setPath('checkout/cart/add', $params);
			//die();
        } else {
            $resultRedirect->setPath('walletsystem/index/index');
        }
        return $resultRedirect;
    }
}
