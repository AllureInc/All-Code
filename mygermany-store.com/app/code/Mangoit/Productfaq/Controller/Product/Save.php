<?php
namespace Mangoit\Productfaq\Controller\Product;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreRepository;

class Save extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $date;
    protected $seller;
    protected $_storeRepository;
    protected $_misProductFaq;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\Seller $seller,
        StoreRepository $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->seller = $seller;
        $this->_storeRepository = $storeRepository;
        $this->_storeManager = $storeManager;
        $this->_misProductFaq = $misProductFaq;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
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
        $isProductFaq = $this->getRequest()->getParam('productfaq');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $date = $this->date->gmtDate();
        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
        $edit = false;
        $customerId = $this->customerSession->getCustomer()->getId();
        //check either customer translated any FAQ or not - Start
        $sellerCustomerRecord = $this->_customerRepositoryInterface->getById($customerId);
        $isTranslated = 0;
        if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
            $isTranslated = $translationObj->getValue();
        }
        //check either customer translated any FAQ or not - End

        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        $sellerData = $this->seller->load($customerId,'seller_id');
        $shoptitle = $sellerData->getShopTitle();
        $stores = $this->_storeRepository->getList();
        if (!$shoptitle) {
            $shoptitle = $sellerData->getShopUrl();
        }
        if ($isProductFaq) {
            if (!empty($data)) {
                if (!$isTranslated) {
                    $currentFaqDetails = $model->load($data['id']);
                    if ($currentFaqDetails->getParentFaqId() == 0) {
                        $misFaqObj = $this->_misProductFaq->load($data['id'],'default_faq_id');
                    } else {
                        $misFaqObj = $this->_misProductFaq->load($currentFaqDetails->getParentFaqId(),'default_faq_id');
                        
                    }
                    $unserilizedFaqs = unserialize($misFaqObj->getStorewiseFaqIds());
                    foreach ($unserilizedFaqs as $unsValue) {
                        $editObj = $model->load($unsValue);
                        $editObj->setTitle($data['title']);
                        $editObj->setDescription($data['description']);
                        $editObj->setIsActive(0);
                        $editObj->setPostedBy($shoptitle);
                        $editObj->setEmailId($customerEmail);
                        $editObj->setVendorId($customerId);
                        $editObj->setUpdatedAt($date);
                        $editObj->setAdminNotification(1);
                        $editObj->save();
                    }
                } else {
                    $editObj = $model->load($data['id']);
                    $editObj->setTitle($data['title']);
                    $editObj->setDescription($data['description']);
                    $editObj->setIsActive(0);
                    $editObj->setPostedBy($shoptitle);
                    $editObj->setEmailId($customerEmail);
                    $editObj->setVendorId($customerId);
                    $editObj->setUpdatedAt($date);
                    $editObj->setAdminNotification(1);
                    $editObj->save();
                }
                $this->messageManager->addSuccess(__('The FAQ has been saved.'));
            } 
        } else {
            if ($isEdit && (!empty($data))) {
                if (!$isTranslated) {
                    $misFaqObj = $this->_misProductFaq->load($data['id'],'default_faq_id');
                    $unserilizedFaqs = unserialize($misFaqObj->getStorewiseFaqIds());
                    foreach ($unserilizedFaqs as $unsValue) {
                        $editObj = $model->load($unsValue);
                        $editObj->setTitle($data['title']);
                        $editObj->setDescription($data['description']);
                        $editObj->setIsActive(0);
                        $editObj->setPostedBy($shoptitle);
                        $editObj->setEmailId($customerEmail);
                        $editObj->setVendorId($customerId);
                        $editObj->setUpdatedAt($date);
                        $editObj->setAdminNotification(1);
                        $editObj->save();
                    }
                } else {
                    $editObj = $model->load($data['id']);
                    $editObj->setTitle($data['title']);
                    $editObj->setDescription($data['description']);
                    $editObj->setIsActive(0);
                    $editObj->setPostedBy($shoptitle);
                    $editObj->setEmailId($customerEmail);
                    $editObj->setVendorId($customerId);
                    $editObj->setUpdatedAt($date);
                    $editObj->setAdminNotification(1);
                    $editObj->save();
                }
                $this->messageManager->addSuccess(__('The FAQ has been saved.'));
            } else {
                if (!empty($data)) {
                    $productids = $data['product_id'];
                    $storeManageObj = $this->_storeManager->getDefaultStoreView();
                    $defaultStoreCode = $storeManageObj->getCode();

                    $allStoreIds = [];
                    // echo "<pre>";
                    // print_r($productids);
                    // // echo "</pre>";

                    foreach ($productids as $key => $val)
                    {   
                        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                        $model->setProductId($val);
                        $model->setTitle($data['title']);
                        $model->setDescription($data['description']);
                        $model->setPublishDate($date);
                        $model->setIsActive(0);
                        $model->setPostedBy($shoptitle);
                        $model->setEmailId($customerEmail);
                        $model->setVendorId($customerId);
                        $model->setPublishDate($date);
                        $model->setUpdatedAt($date);
                        $model->setAdminNotification(1);
                        $model->setStoreId($storeManageObj->getStoreId());
                        $model->setIsTranslated(1);
                        $model->save();
                        $parentFaqId = $model->getId();
                        $allStoreIds[$storeManageObj->getStoreId()] = $model->getId();

                        foreach ($stores as $store) {
                            $storeId = $store->getStoreId();
                            $storeCode = $store->getCode();
                            if ($storeCode == $defaultStoreCode) {
                                continue;
                            }
                            $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                            $model->setProductId($val);
                            $model->setTitle($data['title']);
                            $model->setDescription($data['description']);
                            $model->setPublishDate($date);
                            $model->setIsActive(0);
                            $model->setPostedBy($shoptitle);
                            $model->setEmailId($customerEmail);
                            $model->setVendorId($customerId);
                            $model->setPublishDate($date);
                            $model->setUpdatedAt($date);
                            $model->setAdminNotification(1);
                            $model->setStoreId($storeId);
                            $model->setParentFaqId($parentFaqId);
                            if ($isTranslated) {
                                $model->setIsTranslated(1);
                            }
                            try {
                                $model->save();
                                $allStoreIds[$storeId] = $model->getId();
                            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                                $this->messageManager->addError($e->getMessage());
                            } catch (\RuntimeException $e) {
                                $this->messageManager->addError($e->getMessage());
                            } catch (\Exception $e) {
                                $this->messageManager->addException($e, __('Something went wrong while saving the faq.'));
                            }
                        }
                        //$misFaqObj = $this->_misProductFaq;->load($defaultFaqId,'default_faq_id');
                        // if ($misFaqObj->getId()) {
                            
                        // }
                        try {
                            $misFaqObj = $this->_misProductFaq;
                            $misFaqObj->setDefaultFaqId($parentFaqId);
                            $misFaqObj->setStorewiseFaqIds(serialize($allStoreIds));
                            $misFaqObj->save();
                        } catch (Exception $e) {
                            echo $e->getMessage();
                            die('Exception');
                        }
                    }
                    $this->messageManager->addSuccess(__('The FAQ has been saved.'));
                    return $resultRedirect->setPath('*/*/faq');
                } else {
                    return $resultRedirect->setPath('*/*/faq');
                }  
            }
            return $resultRedirect->setPath('*/*/faq');
        }
    }
}