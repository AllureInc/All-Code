<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Controller\Vendor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreRepository;

/**
 * Mangoit Marketplace Account CheckVendorProfile.php Controller.
 */
class CheckVendorProfile extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $_storeManager;  
    protected $_storeRepository;  
    protected $helper;  

    /**
     * @param Context     $context
     * @param Session     $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StoreRepository $storeRepository,
        \Webkul\Marketplace\Helper\Data $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;     
        $this->_storeRepository = $storeRepository;   
        $this->helper = $helper;   
        parent::__construct($context);
    }
    
    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(
            'Magento\Customer\Model\Url'
        )->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * DeleteSellerLogo action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $storeId = $this->_storeManager->getStore()->getId();
        $stores = $this->_storeRepository->getList();
        $customerData = $this->helper->getCustomer()->getData();
        $isTranslated = 0;
         if (isset($customerData['is_translated'])) {
            if ($customerData['is_translated'] != 0) {
                $isTranslated = 1;
            }
        }

        try {
            $sellerId = $this->_getSession()->getCustomerId();
            $sellerCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
            if (!$isTranslated) {
                $fullCollection = $sellerCollection->getCollection()
                ->addFieldToFilter(
                'seller_id',
                $sellerId
                );
                $status = 0;
                foreach ($fullCollection as $fullValue) {
                    if ($fullValue->getShopTitle() && (!$fullValue->getAccountDeactivate()) && ($fullValue->getContactNumber()) && ($fullValue->getCompanyDescription())) {
                        $status = 1; // Profile is complete
                    } else {
                        $status = 0; //Profile is incomplete
                    }
                }
            } else {
                $sellerCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
                $sellerData = $sellerCollection->getCollection()->addFieldToFilter('seller_id',$sellerId)->addFieldToFilter('store_id',$storeId)->getFirstItem();
                if ($sellerData->getShopTitle() && (!$sellerData->getAccountDeactivate()) && ($sellerData->getContactNumber()) && ($sellerData->getCompanyDescription())) {
                    $status = 1; // Profile is complete
                } else {
                    $status = 0; //Profile is incomplete
                }
            }
            echo $status;
            exit;
            
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode($e->getMessage())
            );
        }
    }
}
