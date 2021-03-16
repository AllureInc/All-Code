<?php
namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\OrderInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Order implements OrderInterface
{

    /**
     * @var orderInterface
     */
    protected $orderInterface;
    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
            

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->orderInterface = $orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $id 'all' can be used to specify 'ALL GROUPS'
     * @param string $orderstatus .
     * @return string Greeting message with users name.
     */
    public function updatestatus($id, $orderstatus) {
        $objectManager = ObjectManager::getInstance();
        $resultJson = $this->resultJsonFactory->create();
        $orderDetails = $this->orderInterface->load($id);
        if ($orderDetails->getId()) {
            $actualOrderStatus = $orderDetails->getStatus();
           if ($actualOrderStatus == 'sent_to_mygermany' && ($orderstatus == 'received')) {
                $orderDetails->setStatus($orderstatus)->setState($orderstatus);
                $orderDetails->save();
                $webkulOrders = $objectManager->create('\Webkul\Marketplace\Model\Orders')->getCollection();
                $webkulOrders->addFieldToFilter('order_id',$orderDetails->getId())->getSelect()->group('seller_id');

                $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
                $salesName = $scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $salesEmail = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                foreach ($webkulOrders as $ordersValue) {
                    $sender = [
                        'name' => $salesName,
                        'email' => $salesEmail,
                    ];
                    $customerObj = $objectManager->create('\Magento\Customer\Model\Customer');
                    $vendorObj = $customerObj->load($ordersValue->getSellerId());
                    $vendorEmail = $vendorObj->getEmail();
                    $vendorName = $vendorObj->getFirstname();
                    $transportBuilder = $objectManager->create('\Magento\Framework\Mail\Template\TransportBuilder');
                    $transportBuilder->clearFrom();
                    $transportBuilder->clearSubject();
                    $transportBuilder->clearMessageId();
                    $transportBuilder->clearBody();
                    $transportBuilder->clearRecipients();
                    $inlineTranslation = $objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
                    $postObject = new \Magento\Framework\DataObject();
                    $postObject->setData(['name'=> $vendorName,'orderid' => $orderDetails->getIncrementId()]);
                    $inlineTranslation->suspend();
                    $transportBuilder->setTemplateIdentifier('marketplace_email_order_received')
                    ->setTemplateOptions(
                      [
                        'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                      ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo('praveenverma@mangoitsolutions.in'); 
                    $transportBuilder->getTransport()->sendMessage();
                    $inlineTranslation->resume();
                }
                return $response = ['success' => 'Successfully saved!'];
            } else if ($actualOrderStatus == 'received' && ($orderstatus == 'order_verified')) {
                $orderDetails->setStatus($orderstatus)->setState($orderstatus);
                $orderDetails->save();
                return $response = ['success' => 'Order successfully verified'];
            } else {
                throw new InputException(__('Cannot update order status!'));
            }
        } else {
            throw new NoSuchEntityException(__('Order not found!'));
        }
    }
}