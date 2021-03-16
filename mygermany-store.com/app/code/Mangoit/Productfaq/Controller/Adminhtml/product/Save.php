<?php
namespace Mangoit\Productfaq\Controller\Adminhtml\Product;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreRepository;

class Save extends \Magento\Framework\App\Action\Action
{

    protected $date;
    protected $seller;
    protected $_storeRepository;
    protected $_misProductFaq;
    protected $authSession;
    /**
     * @var StoreManagerInterface
     */

    protected $_session;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\Seller $seller,
        StoreRepository $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Backend\Model\Auth\Session $authSession, 
        array $data = []
    ) {
        $this->date = $date;
        $this->seller = $seller;
        $this->_storeRepository = $storeRepository;
        $this->_storeManager = $storeManager;
        $this->_misProductFaq = $misProductFaq;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_session = $coreSession;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $date = $this->date->date('Y-m-d');
        $isEdit = $this->getRequest()->getParam('edit');
        $productId = $this->getRequest()->getParam('id');
        $isProductFaq = $this->getRequest()->getParam('productfaq');
        $storeId = $this->getRequest()->getParam('storeId');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $date = $this->date->gmtDate();
        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
        $edit = false;
        $adminUser = $this->authSession->getUser();

        $customerId = 0;
        $isTranslated = 0;

        //check either customer translated any FAQ or not - End
        if ($isProductFaq) {
            if (!empty($data)) {
                $currentFaqDetails = $model->load($data['id']);
                if ($currentFaqDetails->getVendorId() != 0) {
                    $customerId = $currentFaqDetails->getVendorId();
                    //check either customer translated any FAQ or not - Start
                    $sellerCustomerRecord = $this->_customerRepositoryInterface->getById($customerId);
                    if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
                        $isTranslated = $translationObj->getValue();
                    }
                }
                if ($storeId == 0 && ($currentFaqDetails->getVendorId() > 0)) {
                    if ($currentFaqDetails->getParentFaqId() == 0) {
                        $misFaqObj = $this->_misProductFaq->load($data['id'],'default_faq_id');
                    } else {
                        $misFaqObj = $this->_misProductFaq->load($currentFaqDetails->getParentFaqId(),'default_faq_id');
                    }
                    if ($misFaqObj->getStorewiseFaqIds()) {
                        $unserilizedFaqs = unserialize($misFaqObj->getStorewiseFaqIds());
                        foreach ($unserilizedFaqs as $unsValue) {
                            $editObj = $model->load($unsValue);
                            $editObj->setTitle($data['title']);
                            $editObj->setDescription($data['description']);
                            $editObj->setIsActive(1);
                            $editObj->setEmailId($adminUser->getEmail());
                            $editObj->setPostedBy('myGermany gmbh');
                            $editObj->setUpdatedAt($date);
                            // $editObj->setAdminNotification(1);
                            $editObj->save();
                        }
                    } else {
                        //If FAQ created by admin
                        $editObj = $model->load($data['id']);
                        $editObj->setTitle($data['title']);
                        $editObj->setDescription($data['description']);
                        $editObj->setIsActive(1);
                        $editObj->setEmailId($adminUser->getEmail());
                        $editObj->setPostedBy('myGermany gmbh');
                        $editObj->setUpdatedAt($date);
                        $editObj->save();
                    }
                } else {
                    $editObj = $model->load($data['id']);
                    $editObj->setTitle($data['title']);
                    $editObj->setDescription($data['description']);
                    $editObj->setIsActive(1);
                    $editObj->setUpdatedAt($date);
                    // $editObj->setAdminNotification(1);
                    $editObj->save();
                }
            } 
        } else {

            if (!empty($data['product_id'])) {
                foreach ($data['faq_fields'] as $faqVal) {
                    $model->setProductId($data['product_id']);
                    $model->setTitle($faqVal['title']);
                    $model->setDescription($faqVal['description']);
                    $model->setIsActive(1);
                    $model->setUpdatedAt($date);
                    $model->setEmailId($adminUser->getEmail());
                    $model->setPostedBy('myGermany gmbh');
                    $model->setPublishDate($date);
                    // $model->setAdminNotification(1);
                    $model->save();
                    
                }
            } else {
                $this->_session->setNewFaqs($data['faq_fields']);
                echo "1";
                exit;
            }
        }
    }
}