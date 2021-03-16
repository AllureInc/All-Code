<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Fskverified\Block\Adminhtml\Items\Column\Name;

class Seller extends \Webkul\Marketplace\Block\Adminhtml\Items\Column\Name\Seller
{
        /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param array $data
     */
    


    /*public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        array $data = []
    ) {
        $this->saleslistFactory = $saleslistFactory;
        $this->urlInterface = $urlInterface;
        $this->customerModel = $customerModel;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }*/


    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Mangoit_Fskverified::order/view/name.phtml');
        return $this;
    }
   
        public function getUserInfo($id)
    {
        $sellerId = 0;
        $order = $this->getOrder();
        $orderId = $order->getId();
        $marketplaceSalesCollection = $this->saleslistFactory->create()
        ->getCollection()
        ->addFieldToFilter(
            'mageproduct_id',
            ['eq' => $id]
        )
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        );
        if (count($marketplaceSalesCollection)) {
            foreach ($marketplaceSalesCollection as $mpSales) {
                $sellerId = $mpSales->getSellerId();
            }
        }
        if ($sellerId > 0) {
            $customer = $this->customerModel->create()->load($sellerId);
            if ($customer) {
                $returnArray = [];
                $returnArray['name'] = $customer->getName();
                $returnArray['id'] = $sellerId;

                return $returnArray;
            }
        }
    }
    /**
     * Get Customer Url By Customer Id.
     *
     * @param string | $customerId
     *
     * @return string
     */
    public function getCustomerUrl($customerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlbuilder = $objectManager->get(
            'Magento\Framework\UrlInterface'
        );

        return $urlbuilder->getUrl(
            'customer/index/edit',
            ['id' => $customerId]
        );
    }

    public function getObjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}
