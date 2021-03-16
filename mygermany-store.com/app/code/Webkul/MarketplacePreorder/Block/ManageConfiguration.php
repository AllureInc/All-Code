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
namespace Webkul\MarketplacePreorder\Block;

/**
 * Webkul MarketplacePreorder Manage Configuration Block
 */
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;

class ManageConfiguration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $session
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_customer = $customer;
        $this->_session = $session;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        $marketplaceBlock =  $this->_objectManager->create('Webkul\Marketplace\Block\Product\Productlist');
        $products = $marketplaceBlock->getAllProducts();
        return $products;
    }

    public function getProductData($id)
    {
        $marketplaceBlock =  $this->_objectManager->create('Webkul\Marketplace\Block\Product\Productlist');
        $product = $marketplaceBlock->getProductData($id);
        return $product;
    }
}
