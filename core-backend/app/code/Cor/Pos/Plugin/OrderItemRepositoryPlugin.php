<?php
/* File: app/code/Cor/Pos/Plugin/OrderItemRepositoryPlugin.php */

namespace Cor\Pos\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderItemRepositoryPlugin
 */
class OrderItemRepositoryPlugin
{
    /**
     * Order Extension Attributes Factory
     *
     * @var OrderItemExtensionFactory
     */
    protected $extensionFactory;

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var string $_storeCode
     */
    protected $_storeCode;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_signature;

    /**
     * OrderItemRepositoryPlugin constructor
     *
     * @param OrderItemExtensionFactory $extensionFactory
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderItemExtensionFactory $extensionFactory,
        OrderExtensionFactory $orderExtensionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cor\OrderManagement\Model\Signature $signature,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->extensionFactory = $extensionFactory;
        $this->request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_signature = $signature;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->_storeCode = $this->_storeManager->getStore()->getCode();
    }

    /**
     * Add "product_thumbnail" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $moduleName = $this->request->getModuleName();

        $corIncludeSignature = 0;
        if (empty($moduleName)) {
            /* fetch the customer_id in case of API call from COR app */
            $customer_id = $this->request->getParam('customer_id');

            /* fetch the cor_include_sig in case of API call from COR app */
            $corIncludeSignature = $this->request->getParam('cor_include_sig');
            
            /* fetch the cor_one_time_key in case of API call from COR app */
            $cor_one_time_key = $this->request->getParam('cor_one_time_key');

            if (!$customer_id) {
                if ($cor_one_time_key) {
                    $cor_one_time_key_order = $order->getCorOneTimeKey();
                    if (!$cor_one_time_key_order) {
                        /* throws exception when order does not have cor one time key */
                        throw new LocalizedException(__("Requested entity cor item key not found."));
                    } else if ($cor_one_time_key != $cor_one_time_key_order){
                        /* throws exception when post cor one time key does not match with the cor one time key set with order */
                        throw new LocalizedException(__("Requested entity cor item key does not match."));
                    }
                }
            }
        }

        $orderItems = $order->getItems();
        foreach ($orderItems as &$orderItem) {
            $product = $orderItem->getProduct();
            if ($product) {
                $thumbnailImage = $product->getThumbnail();
                $extensionAttributes = $orderItem->getExtensionAttributes();
                $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
                /* get and set the custom thumbnail image parameter to the order items based on the product  */
                $extensionAttributes->setProductThumbnail($thumbnailImage);
                
                /* get and set the product options parameter to the order items */
                $options = [$orderItem->getProductOptions()];
                $extensionAttributes->setProductOptions($options);
                
                $orderItem->setExtensionAttributes($extensionAttributes);
            }
        }

        /* get and set the custom cor_one_time_key parameter to the order data object based on the order itself */
        $orderExtensionAttributes = $order->getExtensionAttributes();
        $orderExtensionAttributes = $orderExtensionAttributes ? $orderExtensionAttributes : $this->orderExtensionFactory->create();

        $corOneTimeKey = $order->getData('cor_one_time_key');
        $orderExtensionAttributes->setCorOneTimeKey($corOneTimeKey);

        // $corIncludeSignature = $order->getData('cor_include_sig');
        if ($corIncludeSignature) {
            $signatureModel = $this->_signature->load($order->getEntityId(), 'magento_order_id');
            if ($signatureModel->getData()) {
                $corCustomerSignature = $signatureModel->getCustomerSignature();
                $orderExtensionAttributes->setCorCustomerSignature($corCustomerSignature);
            }
        }

        $order->setExtensionAttributes($orderExtensionAttributes);

        return $order;
    }

    /**
     * Add "product_thumbnail" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $moduleName = $this->request->getModuleName();

        $corIncludeSignature = 0;
        if (empty($moduleName)) {
            /* fetch the cor_include_sig in case of API call from COR app */
            $corIncludeSignature = $this->request->getParam('cor_include_sig');
        }

        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $orderItems = $order->getItems();
            foreach ($orderItems as &$orderItem) {
                $product = $orderItem->getProduct();
                if ($product) {
                    $thumbnailImage = $product->getThumbnail();
                    $extensionAttributes = $orderItem->getExtensionAttributes();
                    $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
                    /* set the custom thumbnail image parameter to the order items based on the product  */
                    $extensionAttributes->setProductThumbnail($thumbnailImage);
                
                    /* get and set the product options parameter to the order items */
                    $options = [$orderItem->getProductOptions()];
                    $extensionAttributes->setProductOptions($options);

                    $orderItem->setExtensionAttributes($extensionAttributes);
                }
                /* get and set the custom cor_one_time_key parameter to the order data object based on the order itself */
                $orderExtensionAttributes = $order->getExtensionAttributes();
                $orderExtensionAttributes = $orderExtensionAttributes ? $orderExtensionAttributes : $this->orderExtensionFactory->create();
                
                $corOneTimeKey = $order->getData('cor_one_time_key');
                $orderExtensionAttributes->setCorOneTimeKey($corOneTimeKey);

                // $corIncludeSignature = $order->getData('cor_include_sig');
                if ($corIncludeSignature) {
                    $signatureModel = $this->_signature->load($order->getEntityId(), 'magento_order_id');
                    if ($signatureModel->getData()) {
                        $corCustomerSignature = $signatureModel->getCustomerSignature();
                        $orderExtensionAttributes->setCorCustomerSignature($corCustomerSignature);
                    }
                }

                $order->setExtensionAttributes($orderExtensionAttributes);
            }
        }

        return $searchResult;
    }

    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $order_id = $order->getEntityId();

        if (!$order_id) {
            /* generate and set the custom cor_one_time_key parameter to the order data */
            $cor_one_time_key = rand(100000, 999999);
            $order->setCorOneTimeKey($cor_one_time_key);
        }

        return [$order];
    }

    /**
     * Add additional details to order and order item data object
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $default_event = $this->getDefaultEventValue();
        $order_id = $order->getEntityId();

        /*get and set cor signature & cor event id with order */
        $extension_attributes = $order->getExtensionAttributes();

        if ($extension_attributes) {
            $customer_signature = $extension_attributes->getCorCustomerSignature();
            $event_id = $extension_attributes->getCorEventId();

            if ($customer_signature) {
                $model = $this->_signature->load($order_id, 'magento_order_id');

                if (!$model->getData()) {
                    $model->setMagentoOrderId($order_id);
                }

                $model->setCustomerSignature($customer_signature);
                $model->save();
            }

            if ($event_id) {
                $default_event = $event_id;
            }
        }

        $orderItems = $order->getItems();
        foreach ($orderItems as &$orderItem) {
            $product = $orderItem->getProduct();
            if ($product) {
                $cor_artist = $product->getCorArtist();
                $cor_category = $product->getCorCategory();
                if ($cor_artist && $cor_category) {
                    $orderItem->setCorArtistId($cor_artist);
                    $orderItem->setCorArtistCategoryId($cor_category);
                    $orderItem->setCorEventId($default_event);
                    $orderItem->save();
                }
            }
        }

        if (!$order_id) {
            /* generate and set the custom cor_one_time_key parameter to the order data */
            $cor_one_time_key = rand(100000, 999999);
            $order->setCorOneTimeKey($cor_one_time_key);
            $order->save();
        }

        return $order;
    }

    public function getDefaultEventValue()
    {
        $path = 'cor_events/default_event/default_event_id';
        return $this->_scopeConfig->getValue($path, 'store', $this->_storeCode);
    }
}
