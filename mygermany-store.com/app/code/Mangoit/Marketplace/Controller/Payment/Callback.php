<?php
/**
 * Mangoit Callback controller
 *
 * @category    Mangoit
 * @package     Mangoit_Marketplace
 * @author      Mangoit
 */
namespace Mangoit\Marketplace\Controller\Payment;

use CoinGate\Merchant\Model\Payment as CoinGatePayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

class Callback extends \CoinGate\Merchant\Controller\Payment\Callback
{
    protected $order;
    protected $coingatePayment;
    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;
    protected $_sellerOrders;
       /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    protected $_address;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    protected $_logger;
    protected $orderManagement;
    
    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|CoinGatePayment $coingatePayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        CoinGatePayment $coingatePayment,
        MarketplaceHelper $marketplaceHelper,
        \Webkul\Marketplace\Model\Orders $sellerOrders,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order\Address $address,
        ProductRepositoryInterface $productRepository,
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    )
    {
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_sellerOrders = $sellerOrders;
        $this->_customerRepository = $customerRepository;
        $this->_address = $address;
        $this->_productRepository = $productRepository;
        $this->_logger = $logger;
        $this->orderManagement = $orderManagement;
        parent::__construct($context, $order, $coingatePayment, $marketplaceHelper, $sellerOrders, $customerRepository, $address, $productRepository, $logger, $orderManagement, $resource, $resourceCollection);
    }

    public function getObjectManager(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        die("..");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request_order_id = (filter_input(INPUT_POST, 'order_id') ? filter_input(INPUT_POST, 'order_id') : filter_input(INPUT_GET, 'order_id'));
        
        $order = $this->order->loadByIncrementId($request_order_id);
        //Send Email to vendor or order sell- Start
        $lastOrderId = $order->getId();
        // send email
        $request_id = (filter_input(INPUT_POST, 'id') ? filter_input(INPUT_POST, 'id') :  filter_input(INPUT_GET, 'id'));
        $cgOrder = \CoinGate\Merchant\Order::find($request_id);
        $this->_logger->addDebug('OrderStatus: '.$request_id);
        if ($cgOrder->status == 'canceled') { 
            $this->orderManagement->cancel($order->getEntityId());
        } 
        if ($cgOrder->status == 'paid') {
            /*$adminUsername = 'Admin';*/
            $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

            $shippingInfo = '';
            $shippingDes = '';
            $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
            $defaultTransEmailId = $this->_marketplaceHelper->getDefaultTransEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;

            $billingId = $order->getBillingAddress()->getId();
            $billaddress = $this->_address->load($billingId);
            $billinginfo = $billaddress['firstname'].'<br/>'.
            $billaddress['street'].'<br/>'.
            $billaddress['city'].' '.
            $billaddress['region'].' '.
            $billaddress['postcode'].'<br/>'.
            $this->getObjectManager()->create(
                'Magento\Directory\Model\Country'
            )->load($billaddress['country_id'])->getName().'<br/>T:'.
            $billaddress['telephone'];


            if ($order->getShippingAddress()) {
                $shippingId = $order->getShippingAddress()->getId();
                $address = $this->getObjectManager()->create(
                    'Magento\Sales\Model\Order\Address'
                )->load($shippingId);
                $shippingInfo = $address['firstname'].'<br/>'.
                $address['street'].'<br/>'.
                $address['city'].' '.
                $address['region'].' '.
                $address['postcode'].'<br/>'.
                $this->getObjectManager()->create(
                    'Magento\Directory\Model\Country'
                )->load($address['country_id'])->getName().'<br/>T:'.
                $address['telephone'];
                $shippingDes = $order->getShippingDescription();
            }
            $paymentCode = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            $paymentMethodTitle = $order->getPayment()->getMethodInstance()->getTitle();
            $sellerOrder = $this->_sellerOrders->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('seller_id', ['neq' => 0]);
            //vendor product delivery days- Part 1 - start
            $vendorProductDelDays = '';
            if ($order->getShippingMethod() == 'warehouse_warehouse') {
                $vendorProductDelDays = unserialize($order->getVendorDeliveryDays());
            }
            //vendor product delivery days- Part 1 - End
            foreach ($sellerOrder as $info) {
                $totalprice = 0;
                $totalTaxAmount = 0;
                $codCharges = 0;
                $shippingCharges = 0;
                $orderinfo = '';
                $saleslistIds = [];
                $collection1 = $this->getObjectManager()->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )->getCollection()
                ->addFieldToFilter('order_id', $lastOrderId)
                ->addFieldToFilter('seller_id', $info['seller_id'])
                ->addFieldToFilter('parent_item_id', ['null' => 'true'])
                ->addFieldToFilter('magerealorder_id', ['neq' => 0])
                ->addFieldToSelect('entity_id');

                $saleslistIds = $collection1->getData();

                $fetchsale = $this->getObjectManager()->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $saleslistIds]
                );

                $userdata = $this->_customerRepository->getById($info['seller_id']);
                $username = $userdata->getFirstname();
                $useremail = $userdata->getEmail();
                $sellerStoreId = $userdata->getStoreId();

                $senderInfo = [];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

                $receiverInfo = [];
                $receiverInfo = [
                    'name' => $username,
                    'email' => $useremail,
                ];

                $fetchsale->getSellerOrderCollection();
                foreach ($fetchsale as $res) {
                    $product = $this->_productRepository->getById($res['mageproduct_id']);

                    /* product name */
                    $productName = $res->getMageproName();
                    $result = [];
                    $result = $this->getProductOptionData($res, $result);
                    $productName = $this->getProductNameHtml($result, $productName);
                    /* end */

                    $sku = $product->getSku();
                    $orderinfo = $orderinfo."<tbody><tr>
                                    <td class='item-info'>".$productName."</td>
                                    <td class='item-info'>".$sku."</td>
                                    <td class='item-qty'>".($res['magequantity'] * 1)."</td>
                                    <td class='item-price'>".
                                        $order->formatPrice(
                                            $res['magepro_price'] * $res['magequantity']
                                        ).
                                    '</td>
                                 </tr></tbody>';
                    $totalTaxAmount = $totalTaxAmount + $res['total_tax'];
                    $totalprice = $totalprice + ($res['magepro_price'] * $res['magequantity']);
                }
                $shippingCharges = $info->getShippingCharges();
                $totalCod = 0;

                if ($paymentCode == 'mpcashondelivery') {
                    $totalCod = $info->getCodCharges();
                    $codRow = "<tr class='subtotal'>
                                <th colspan='3'>".__('Cash On Delivery Charges')."</th>
                                <td colspan='3'><span>".
                                    $order->formatPrice($totalCod).
                                '</span></td>
                                </tr>';
                } else {
                    $codRow = '';
                }

                $orderinfo = $orderinfo."<tfoot class='order-totals'>
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Shipping & Handling Charges')."</th>
                                        <td colspan='3'><span>".
                                        $order->formatPrice($shippingCharges)."</span></td>
                                    </tr>
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Tax Amount')."</th>
                                        <td colspan='3'><span>".
                                        $order->formatPrice($totalTaxAmount).'</span></td>
                                    </tr>'.$codRow."
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Grandtotal')."</th>
                                        <td colspan='3'><span>".
                                        $order->formatPrice(
                                            $totalprice +
                                            $totalTaxAmount +
                                            $shippingCharges +
                                            $totalCod
                                        ).'</span></td>
                                    </tr></tfoot>';

                $isNotVirtual = 0;
                //vendor product delivery days- Part 2 - Start
                $emailTempVariables['vendor_shop_title'] = '';
                $emailTempVariables['delivery_days'] = 0;
                $emailTempVariables['warehouse'] = 0;
                if (is_array($vendorProductDelDays)) {
                    $emailTempVariables['warehouse'] = 1;
                    $individualVendoDeliveryDetails = $vendorProductDelDays[$info['seller_id']];
                    $emailTempVariables['vendor_shop_title'] = $individualVendoDeliveryDetails['shop_title'];
                    $emailTempVariables['delivery_days'] = $individualVendoDeliveryDetails['final_days'];
                    // foreach ($individualVendoDeliveryDetails as $key => $value) {
                    // }
                }
                //vendor product delivery days- Part 2 - End
                $emailTempVariables['myvar1'] = $order->getRealOrderId();
                $emailTempVariables['myvar2'] = $order['created_at'];
                $emailTempVariables['myvar4'] = $billinginfo;
                $emailTempVariables['myvar5'] = $paymentMethodTitle;
                $emailTempVariables['myvar6'] = $shippingInfo;
                $emailTempVariables['isNotVirtual'] = $isNotVirtual;
                $emailTempVariables['myvar9'] = $shippingDes;
                $emailTempVariables['myvar8'] = $orderinfo;
                $emailTempVariables['myvar3'] = $username;
                $this->getObjectManager()->get(
                        'Webkul\Marketplace\Helper\Email'
                    )->sendPlacedOrderEmail(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo,
                        $sellerStoreId
                    );
            }
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $order->save();
        }
        $this->coingatePayment->validateCoinGateCallback($order);
        //Send Email to vendor or order sell- Start
        $this->getResponse()->setBody('OK');
    }
     /**
     * Get Order Product Option Data Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $result
     *
     * @return array
     */
    public function getProductOptionData($item, $result = [])
    {
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();*/
        $productOptionsData = $this->getObjectManager()->get(
            'Webkul\Marketplace\Helper\Orders'
        )->getProductOptions(
            $item->getProductOptions()
        );
        if ($options = $productOptionsData) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }
     /**
     * Get Order Product Name Html Data Method.
     *
     * @param array  $result
     * @param string $productName
     *
     * @return string
     */
    public function getProductNameHtml($result, $productName)
    {
        if ($_options = $result) {
            $proOptionData = '<dl class="item-options">';
            foreach ($_options as $_option) {
                $proOptionData .= '<dt>'.$_option['label'].'</dt>';

                $proOptionData .= '<dd>'.$_option['value'];
                $proOptionData .= '</dd>';
            }
            $proOptionData .= '</dl>';
            $productName = $productName.'<br/>'.$proOptionData;
        } else {
            $productName = $productName.'<br/>';
        }

        return $productName;
    }
}
