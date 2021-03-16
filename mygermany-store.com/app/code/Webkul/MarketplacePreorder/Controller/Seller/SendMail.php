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
namespace Webkul\MarketplacePreorder\Controller\Seller ;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MarketplacePreorder\Api\PreorderItemsManagementInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;

use Magento\Framework\App\RequestInterface;

class SendMail extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** @var PreorderSellerInterfaceFactory  */
    protected $_preorderItemsFactory;

    /**
     * @var PreorderSellerManagementInterface
     */
    protected $sellerManagement;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var PreorderItemsCollection
     */
    protected $_preorderItemCollection;

    /**
     * @var ItemsRepository
     */
    protected $_itemsRepository;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Email
     */
    protected $_emailHelper;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Email
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $_notInStockProducts = [];

    /**
     *
     * @param Context                                  $context
     * @param PageFactory                              $resultPageFactory
     * @param PreorderItemsCollection                  $preorderItemCollection
     * @param PreorderItemsInterfaceFactory            $preorderItemsFactory
     * @param ItemsRepository                          $itemsRepository
     * @param DataObjectHelper                         $dataObjectHelper
     * @param \Magento\Customer\Model\Session          $customerSession
     * @param \Webkul\MarketplacePreorder\Helper\Data  $preorderHelper
     * @param \Webkul\MarketplacePreorder\Helper\Email $emailHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PreorderItemsCollection $preorderItemCollection,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        ItemsRepository $itemsRepository,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        \Webkul\MarketplacePreorder\Helper\Email $emailHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customerSession = $customerSession;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->_preorderItemCollection = $preorderItemCollection;
        $this->_itemsRepository = $itemsRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_preorderHelper = $preorderHelper;
        $this->_emailHelper = $emailHelper;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default seller DHL config Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $this->_logger->info("#### Send Email Controller = MarketplacePreorder ####");
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->isPost()) {
            $preorderIds = $this->getRequest()->getParam('product_mass_email');
            if (count($preorderIds) > 0) {
                $this->_logger->info("## preorderIds : ".json_encode($preorderIds));
                $result = $this->sendMailToCustomers($preorderIds);
                
                if(is_array($result)){
                    if ($result['status']) {
                        if(count($result['products']))
                            $this->messageManager->addSuccess(
                            __('Your preorder notification has been sent. Except for these products '.implode(',',$errorProductId))
                        );
                        else
                            $this->messageManager->addSuccess(
                            __('Your preorder Notificaation has been sent successfully.')
                        );
                    }
                }
                else{
                    $this->messageManager->addError(
                            __('Can not send the Notification for the Out of stock product.'));
                }
            } else {
                $this->messageManager->addError(
                    __('Please select Preorder orders.')
                );
            }
            $this->_notInStockProducts = array_unique($this->_notInStockProducts);
            if (count($this->_notInStockProducts)) {
                foreach ($this->_notInStockProducts as $product) {
                    $this->messageManager->addError(
                        __('Product "%1" is out of stock.', $product)
                    );
                }
            }

        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/orders',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    /**
     *
     * @param  array $preorderIds
     * @return bool
     */
    public function sendMailToCustomers($preorderIds)
    {
        $customerEmails = [];
        $productArray = [];
        $preOrderIdArray = [];
        $errorProductId = [];
        $collection = $this->_preorderItemCollection->create();
        $collection->addFieldToFilter('id', ['in' => $preorderIds]);
        
        foreach ($collection as $item) {
            $productId = $item->getProductId();
            $product = $this->_preorderHelper->getProduct($productId);
            $this->_logger->info("#### Preorder Product Name: ".$product->getName()." ####");
          
            $stockDetails = $this->_preorderHelper->getStockDetails($productId);
            if ($stockDetails['is_in_stock'] == 1) {
                $customerEmails[] = $item->getCustomerEmail();
                $this->_logger->info("#### Customer Email: ".$item->getCustomerEmail()." ####");
                $productArray[] = $productId;
                $preOrderIdArray[] = $item->getId();
                if ($item->getType()==0) {
                    $updateData = [
                        'status' => 1,
                    ];
                    $this->updatePreorderItem($item->getId(), $updateData);
                }
            } else {
                $this->_notInStockProducts[] = $product->getName();
                $errorProductId[] = $item->getProductId();
            }
        }
        $errorProductId = array_unique($errorProductId);
        if (count($customerEmails) > 0 /*&& count($this->_notInStockProducts) == 0*/) {
            $this->_emailHelper->notifyBuyers($customerEmails, $productArray, $preOrderIdArray);
            foreach ($preOrderIdArray as $itemId) {
                $updateData = [
                    'notify' => 1,
                ];
                $this->updatePreorderItem($itemId, $updateData);
            }
            return ['status'=> true ,'products'=>$errorProductId];
        }
        return false;
    }

    public function updatePreorderItem($itemId, $updatedData)
    {
        $itemData = $this->_itemsRepository->getById($itemId);
        $savedData = (array) $itemData->getData();

        $itemsDataObject = $this->_preorderItemsFactory->create();

        $mergeData = array_merge(
            $savedData,
            $updatedData
        );
        $mergeData['id'] = $itemId;
        $this->dataObjectHelper->populateWithArray(
            $itemsDataObject,
            $mergeData,
            '\Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
        );
        try {
            $this->_itemsRepository->save($itemsDataObject);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
