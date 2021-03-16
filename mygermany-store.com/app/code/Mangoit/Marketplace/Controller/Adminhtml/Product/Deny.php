<?php
namespace Mangoit\Marketplace\Controller\Adminhtml\Product;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Webkul\Marketplace\Model\ProductFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Magento\Catalog\Model\CategoryFactory;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;

class Deny extends \Webkul\Marketplace\Controller\Adminhtml\Product\Deny
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @var ProductFactory
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productAction;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var MpEmailHelper
     */
    protected $mpEmailHelper;

    /**
     * @param Context                                     $context
     * @param Filter                                      $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param Processor                                   $productPriceIndexerProcessor
     * @param ProductFactory                              $productModel
     * @param \Magento\Catalog\Model\Product\Action       $productAction
     * @param MpHelper                                    $mpHelper
     * @param NotificationHelper                          $notificationHelper
     * @param CategoryFactory                             $categoryFactory
     * @param MpEmailHelper                               $mpEmailHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Processor $productPriceIndexerProcessor,
        ProductFactory $productModel,
        \Magento\Catalog\Model\Product\Action $productAction,
        MpHelper $mpHelper,
        NotificationHelper $notificationHelper,
        CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        MpEmailHelper $mpEmailHelper
    ) {
        $this->filter = $filter;
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->dateTime = $dateTime;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productModel = $productModel;
        $this->productAction = $productAction;
        $this->mpHelper = $mpHelper;
        $this->notificationHelper = $notificationHelper;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->customerModel = $customerModel;
        $this->mpEmailHelper = $mpEmailHelper;
        parent::__construct($context, $filter, $date, $dateTime, $storeManager, $productRepository, $productPriceIndexerProcessor, $productModel, $productAction, $mpHelper, $notificationHelper, $categoryFactory, $productFactory, $customerModel, $mpEmailHelper);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter('mageproduct_id', $data['mageproduct_id'])
        ->addFieldToFilter('seller_id', $data['seller_id']);
        if ($collection->getSize()) {
            $productIds = [$data['mageproduct_id']];
            $allStores = $this->_storeManager->getStores();
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

            $sellerProduct = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection();

            $coditionData = "`mageproduct_id`=".$data['mageproduct_id'];

            $sellerProduct->setProductData(
                $coditionData,
                ['status' => $status, 'seller_pending_notification' => 1]
            );
            foreach ($allStores as $eachStoreId => $storeId) {
                $this->_objectManager->get(
                    'Magento\Catalog\Model\Product\Action'
                )->updateAttributes($productIds, ['status' => $status], $storeId);
            }

            $this->_objectManager->get(
                'Magento\Catalog\Model\Product\Action'
            )->updateAttributes($productIds, ['status' => $status], 0);

            $this->_productPriceIndexerProcessor->reindexList($productIds);

            $catagoryModel = $this->_objectManager->get('Magento\Catalog\Model\Category');

            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

            $id = 0;

            foreach ($collection as $item) {
                $id = $item->getId();
                $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Notification'
                )->saveNotification(
                    \Webkul\Marketplace\Model\Notification::TYPE_PRODUCT,
                    $id,
                    $data['mageproduct_id']
                );
            }
            
            $model = $this->_objectManager->get(
                'Magento\Catalog\Model\Product'
            )->load($data['mageproduct_id']);

            $catarray = $model->getCategoryIds();
            $categoryname = '';
            foreach ($catarray as $keycat) {
                $categoriesy = $catagoryModel->load($keycat);
                if ($categoryname == '') {
                    $categoryname = $categoriesy->getName();
                } else {
                    $categoryname = $categoryname.','.$categoriesy->getName();
                }
            }
            $allStores = $this->_storeManager->getStores();

            $pro = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->load($id);

            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            $adminStoreEmail = $helper->getAdminEmailId();
            $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

            $seller = $this->_objectManager->get(
                'Magento\Customer\Model\Customer'
            )->load($data['seller_id']);
            $emailTempVariables['myvar1'] = $seller->getName();
            $emailTempVariables['myvar2'] = $data['product_deny_reason'];
            $emailTempVariables['myvar3'] = $model->getName();
            $emailTempVariables['myvar4'] = $categoryname;
            $emailTempVariables['myvar5'] = $model->getDescription();
            $emailTempVariables['myvar6'] = $model->getPrice();
            $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

            $sellerStoreId = $seller->getData('store_id');

            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendProductDenyMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $sellerStoreId
            );

            $this->_eventManager->dispatch(
                'mp_deny_product',
                ['product' => $pro, 'seller' => $seller]
            );

            $this->messageManager->addSuccess(__('Product has been Denied.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::product');
    }
}
