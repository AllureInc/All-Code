<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreRepository;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Mangoit Marketplace Account DeleteSellerBanner Controller.
 */
class DeleteSellerBanner extends \Webkul\Marketplace\Controller\Account\DeleteSellerBanner
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    protected $_storeRepository;  
    protected $_storeManager;

    /**
     * @param Context     $context
     * @param Session     $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        CustomerUrl $customerUrl,
        SellerFactory $sellerModel,
        JsonHelper $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StoreRepository $storeRepository
    ) {
        $this->_storeRepository = $storeRepository;   
        $this->helper = $helper;  
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        $this->sellerModel = $sellerModel;
        $this->jsonHelper = $jsonHelper;
        /*parent::__construct($context, $customerSession, $resultPageFactory, $storeManager );*/
        parent::__construct($context, $customerSession, $resultPageFactory, $helper, $customerUrl, $sellerModel, $jsonHelper);
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
     * DeleteSellerBanner action.
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
            $autoId = '';
            $sellerId = $this->_getSession()->getCustomerId();
            $sellerCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
            if (!$isTranslated) {
                $fullCollection = $sellerCollection->getCollection()
                ->addFieldToFilter(
                'seller_id',
                $sellerId
                );
                foreach ($fullCollection as $fullValue) {
                    $fullValue->setBannerPic('');
                    $fullValue->save();
                }
            } else{
                $sellerColl = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection()->addFieldToFilter('seller_id',$sellerId)
                ->addFieldToFilter('store_id',$this->_storeManager->getStore()->getId());
                foreach ($sellerColl as $key => $value) {
                    $value->setBannerPic('');
                    $value->save();
                }
                // if ($autoId != '') {
                    // $value = $this->_objectManager->create(
                    //     'Webkul\Marketplace\Model\Seller'
                    // )->load($sellerId, 'seller_id');
                // }
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode(true)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode($e->getMessage())
            );
        }
    }
}
