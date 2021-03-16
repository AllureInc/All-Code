<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Class MassApprove.
 */
class MassApprove extends \Webkul\Marketplace\Controller\Adminhtml\Product\MassApprove
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $productPriceIndexerProcessor;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productAction;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $eavProcessor;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Email
     */
    protected $mpEmailHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Notification
     */
    protected $mpNotificationHelper;

    /**
     * @var productFaq
     */
    protected $productFaq;

    /**
     * @var misProductFaq
     */
    protected $_misProductFaq;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Processor $productPriceIndexerProcessor
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Catalog\Model\Product\Action $productAction
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavProcessor
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Helper\Email $mpEmailHelper
     * @param \Webkul\Marketplace\Helper\Notification $mpNotificationHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        Processor $productPriceIndexerProcessor,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavProcessor,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Helper\Email $mpEmailHelper,
        \Webkul\Marketplace\Helper\Notification $mpNotificationHelper,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq
    ) {
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->mpProductFactory = $mpProductFactory;
        $this->productAction = $productAction;
        $this->eavProcessor = $eavProcessor;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->mpHelper = $mpHelper;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->mpNotificationHelper = $mpNotificationHelper;
        $this->productFaq = $productFaq;
        $this->_misProductFaq = $misProductFaq;
        parent::__construct($context, $filter, $storeManager, $collectionFactory, $productPriceIndexerProcessor, $mpProductFactory, $productAction, $eavProcessor, $customerFactory, $productFactory, $categoryFactory, $mpHelper, $mpEmailHelper, $mpNotificationHelper, $productFaq, $misProductFaq);
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
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();

        $priceHelper = $this->_objectManager->get(
            'Magento\Framework\Pricing\Helper\Data'
        );

        $currencySymbol = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $curSymbol = $currencySymbol->getStore()->getCurrentCurrency()->getCurrencySymbol();

        $sellerProductModel = $this->_objectManager->get(
            'Webkul\Marketplace\Model\Product'
        );
        $magentoProductModel = $this->_objectManager->get(
            'Magento\Catalog\Model\Product'
        );
        $allStores = $this->storeManager->getStores();
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;

        $sellerProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection();

        $coditionArr = [];
        foreach ($productIds as $key => $id) {
            $condition = "`mageproduct_id`=".$id;
            array_push($coditionArr, $condition);
        }
        $coditionData = implode(' OR ', $coditionArr);

        $sellerProduct->setProductData(
            $coditionData,
            ['status' => $status, 'seller_pending_notification' => 1]
        );
        foreach ($allStores as $eachStoreId => $storeId) {
            $this->_objectManager->get(
                'Magento\Catalog\Model\Product\Action'
            )->updateAttributes($productIds, ['status' => $status], $storeId->getId());
        }
        $this->_objectManager->get(
            'Magento\Catalog\Model\Product\Action'
        )->updateAttributes($productIds, ['status' => $status], 0);

        $this->productPriceIndexerProcessor->reindexList($productIds);

        $this->_objectManager->get(
            'Magento\Catalog\Model\Indexer\Product\Eav\Processor'
        )->reindexList($productIds);

        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');

        $catagoryModel = $this->_objectManager->get('Magento\Catalog\Model\Category');

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        foreach ($collection as $item) {
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Notification'
            )->saveNotification(
                \Webkul\Marketplace\Model\Notification::TYPE_PRODUCT,
                $item->getId(),
                $item->getMageproductId()
            );
            $pro = $sellerProductModel->load($item->getId());
            $productModel = $magentoProductModel->load($item->getMageproductId());
            $productModel->setStatus(1);
            $productModel->save();
            $catarray = $productModel->getCategoryIds();
            $categoryname = '';
            foreach ($catarray as $keycat) {
                $categoriesy = $catagoryModel->load($keycat);
                if ($categoryname == '') {
                    $categoryname = $categoriesy->getName();
                } else {
                    $categoryname = $categoryname.','.$categoriesy->getName();
                }
            }
            $adminStoreEmail = $helper->getAdminEmailId();
            $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

            $seller = $customerModel->load(
                $item->getSellerId()
            );

            $emailTemplateVariables = [];
            $emailTemplateVariables['myvar1'] = $productModel->getName();
            $emailTemplateVariables['myvar2'] = $productModel->getDescription();
            $emailTemplateVariables['myvar3'] = number_format($productModel->getPrice(),2).' EUR';
            $emailTemplateVariables['myvar4'] = $categoryname;
            $emailTemplateVariables['myvar5'] = $seller->getname();
            $emailTemplateVariables['myvar6'] =
            'I would like to inform you that your product has been approved.';

            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $sellerStoreId = $seller->getData("store_id");
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendProductStatusMail(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo,
                $sellerStoreId
            );

            $this->_eventManager->dispatch(
                'mp_approve_product',
                ['product' => $pro, 'seller' => $seller]
            );
            // Product FAQs approval- Start
                $currentFaqDetails = $this->productFaq->getCollection();
                $currentFaqDetails->addFieldToFilter('product_id',$item->getMageproductId());
                if ($currentFaqDetails->count() > 0) {
                    foreach ($currentFaqDetails as $faqValue) {
                        $faqValue->setIsActive(true);
                        $faqValue->save();
                    }
                }
            // Product FAQs approval- End
        }
        /* date: 12-Oct-2018 For approved status of child products*/
        $this->setApproveStatusOnChild($productIds, $magentoProductModel, $sellerProductModel);

        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been approved.',
                $collection->getSize()
            )
        );
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    public function setApproveStatusOnChild($productIds, $magentoProductModel, $sellerProductModel)
    {
        foreach ($productIds as $key => $id) {
            $magentoProductModel->load($id);
            if ($magentoProductModel->getProductType() == 'configurable') {
                $idArray = $magentoProductModel->getTypeInstance()->getUsedProducts($magentoProductModel);
                if (!empty($idArray)) {
                    foreach ($idArray as $child) {
                        if ($sellerProductModel->load($child->getId(), 'mageproduct_id')) {
                            $sellerProductModel->setStatus(1);
                            $sellerProductModel->save();
                        }
                    }
                }
            }
        }
    }

}
