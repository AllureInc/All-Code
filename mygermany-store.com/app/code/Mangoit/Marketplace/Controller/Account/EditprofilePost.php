<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited
 */

namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreRepository;
use Mangoit\Marketplace\Helper\Data as MarketPlaceHelper;

/**
 * Webkul Marketplace Account EditprofilePost Controller.
 */
class EditprofilePost extends \Webkul\Marketplace\Controller\Account\EditprofilePost
{
    /**
     * @var Rate
     */
    protected $_storeRepository;

    protected $_transportBuilder;

    protected $_marketPlaceHelper;

    /**
     * @param Context                                          $context
     * @param Session                                          $customerSession
     * @param FormKeyValidator                                 $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date
     * @param Filesystem                                       $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        StoreRepository $storeRepository,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        MarketPlaceHelper $marketplaceHelper
    ) {
        $this->_storeRepository = $storeRepository;
        $this->_transportBuilder = $transportBuilder;
        $this->_marketPlaceHelper = $marketplaceHelper;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $date,
            $filesystem,
            $fileUploaderFactory,
            $helper
        );
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
     * Update Seller Profile Informations.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                $fields = $this->getRequest()->getParams();
                $stores = $this->_storeRepository->getList();
                // $this->_storeRepository->getStores();
                
                $errors = $this->validateprofiledata($fields);
                $sellerId = $this->helper->getCustomerId();
                $storeId = $this->helper->getCurrentStoreId();
                $customerData = $this->helper->getCustomer()->getData();
                $isTranslated = 0;
                 if (isset($customerData['is_translated'])) {
                    if ($customerData['is_translated'] != 0) {
                        $isTranslated = 1;
                    }
                } 
                $img1 = '';
                $img2 = '';

                if (empty($errors)) {

                    $images = [];
                    foreach ($stores as $store) {
                        if (isset($fields['mis_store_id'])) {
                            if (!$fields['mis_store_id']) {
                                $storeId = $store->getStoreId();
                            } else {
                                $storeId = $fields['mis_store_id'];
                            }
                        } else{
                            $storeId = $store->getStoreId();
                        }
                        // $storeId = $store->getStoreId();
                        $autoId = 0;
                        $trustworthy = 0;
                        $collection = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Seller'
                        )
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('store_id', $storeId);
                        foreach ($collection as $value) {
                            $autoId = $value->getId();
                            $trustworthy = $value->getTrustworthy();
                        }
                        $fields = $this->getSellerProfileFields($fields);
                        // If seller data doesn't exist for current store
                        if (!$autoId) {
                            $sellerDefaultData = [];
                            $collection = $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Seller'
                            )
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->addFieldToFilter('store_id', 0);
                            foreach ($collection as $value) {
                                $sellerDefaultData = $value->getData();
                            }
                            foreach ($sellerDefaultData as $key => $value) {
                                if (empty($fields[$key]) && $key != 'entity_id') {
                                    $fields[$key] = $value;
                                }
                            }
                        }

                        // Save seller data for current store
                        $value = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Seller'
                        )->load($autoId);
                        $value->addData($fields);
                        if (!$autoId) {
                            $value->setCreatedAt($this->_date->gmtDate());
                        }
                        $value->setUpdatedAt($this->_date->gmtDate());
                        $value->save();

                        if ($fields['company_description']) {
                            $fields['company_description'] = str_replace(
                                'script',
                                '',
                                $fields['company_description']
                            );
                        }
                        $value->setCompanyDescription($fields['company_description']);

                        if (isset($fields['return_policy'])) {
                            $fields['return_policy'] = str_replace(
                                'script',
                                '',
                                $fields['return_policy']
                            );
                            $value->setReturnPolicy($fields['return_policy']);
                        }

                        if (isset($fields['shipping_policy'])) {
                            $fields['shipping_policy'] = str_replace(
                                'script',
                                '',
                                $fields['shipping_policy']
                            );
                            $value->setShippingPolicy($fields['shipping_policy']);
                        }

                        $value->setMetaDescription($fields['meta_description']);
                        if ($trustworthy) {
                            $value->setIsProfileApproved(1);
                            $value->setProfileAdminNotification(0);
                        } else {
                            $value->setIsProfileApproved(0);
                            $value->setProfileAdminNotification(1);
                        }


                        /**
                         * set taxvat number for seller
                         */
                        if ($fields['taxvat']) {
                            $customer = $this->_objectManager->create(
                                'Magento\Customer\Model\Customer'
                            )->load($sellerId);
                            $customer->setTaxvat($fields['taxvat']);
                            $customer->setId($sellerId)->save();
                        }

                        $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                        try {
                            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                            $uploader = $this->_fileUploaderFactory->create(
                                ['fileId' => 'banner_pic']
                            );
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploader->setAllowRenameFiles(true);
                            $result = $uploader->save($target);

                            if ($result['file']) {
                                $images['banner_pic'] = $result['file'];
                                $parentPath = $target.$images['banner_pic'];
                                $value->setBannerPic($result['file']);
                            } 
                        } catch (\Exception $e) {
                            if ($e->getMessage() != 'The file was not uploaded.') {
                                $this->messageManager->addError($e->getMessage());
                            } else {
                                if (isset($images['banner_pic'])) {
                                    $value->setBannerPic($images['banner_pic']);
                                    //The name of the directory that we need to create.
                                    $directoryName = $target;
                                     
                                    //Check if the directory already exists.
                                    if(!is_dir($directoryName)){
                                        //Directory does not exist, so lets create it.
                                        mkdir($directoryName, 0777, true);
                                    }
                                    $file = $parentPath;
                                    $newfile = $target.$images['banner_pic'];
                                     
                                    if (!copy($file, $newfile)) {
                                        echo "failed to copy $file...\n";
                                    }else{
                                        echo "copied $file into $newfile\n";
                                    }
                                }
                            }
                        }
                        try {
                            /** @var $uploaderLogo \Magento\MediaStorage\Model\File\Uploader */
                            $uploaderLogo = $this->_fileUploaderFactory->create(
                                ['fileId' => 'logo_pic']
                            );
                            $uploaderLogo->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploaderLogo->setAllowRenameFiles(true);
                            $resultLogo = $uploaderLogo->save($target);
                            if ($resultLogo['file']) {
                                $images['logo_pic'] = $resultLogo['file'];
                                $parentPath = $target.$images['logo_pic'];
                                $value->setLogoPic($resultLogo['file']);
                            }
                        } catch (\Exception $e) {
                            if ($e->getMessage() != 'The file was not uploaded.') {
                                $this->messageManager->addError($e->getMessage());
                            } else {
                                if (isset($images['logo_pic'])) {
                                    $value->setLogoPic($images['logo_pic']);
                                    //The name of the directory that we need to create.
                                    $directoryName = $target;
                                     
                                    //Check if the directory already exists.
                                    if(!is_dir($directoryName)){
                                        //Directory does not exist, so lets create it.
                                        mkdir($directoryName, 0777, true);
                                    }
                                    $file = $parentPath;
                                    $newfile = $target.$images['logo_pic'];
                                     
                                    if (!copy($file, $newfile)) {
                                        echo "failed to copy $file...\n";
                                    }else{
                                        echo "copied $file into $newfile\n";
                                    }
                                }
                            }
                        }

                        try {

                            /** @var $uploaderBgImg \Magento\MediaStorage\Model\File\Uploader */
                            $uploaderBgImg = $this->_fileUploaderFactory->create(
                                ['fileId' => 'shop_background_img']
                            );




                            $uploaderBgImg->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploaderBgImg->setAllowRenameFiles(true);
                            $resultBgImg = $uploaderBgImg->save($target);

                            if ($resultBgImg['file']) {
                                $images['shop_bg'] = $resultBgImg['file'];
                                $parentPath = $target.$images['shop_bg'];
                                $value->setShopBackgroundImg($resultBgImg['file']);
                            }
                        } catch (\Exception $e) {
                            if ($e->getMessage() == 'Disallowed file type.') {
                                $this->messageManager->addError($e->getMessage());
                            } else if ($e->getMessage() != 'The file was not uploaded.') {
                                $this->messageManager->addError('Disallowed file type for background image');
                            } else {
                                if (isset($images['shop_bg'])) {
                                    $value->setShopBackgroundImg($images['shop_bg']);
                                    //The name of the directory that we need to create.
                                    $directoryName = $target;
                                     
                                    //Check if the directory already exists.
                                    if(!is_dir($directoryName)){
                                        //Directory does not exist, so lets create it.
                                        mkdir($directoryName, 0777, true);
                                    }
                                    $file = $parentPath;
                                    $newfile = $target.$images['shop_bg'];
                                     
                                    if (!copy($file, $newfile)) {
                                        echo "failed to copy $file...\n";
                                    }else{
                                        echo "copied $file into $newfile\n";
                                    }
                                }
                            }
                        }

                        try {
                            $watermarkPath = 'catalog/product/watermark/stores/'.$storeId.'/';
                            $watermarkImageTarget = $this->_mediaDirectory->getAbsolutePath($watermarkPath);
                            /** @var $uploaderBgImg \Magento\MediaStorage\Model\File\Uploader */
                            $watermarkUploadImg = $this->_fileUploaderFactory->create(
                                ['fileId' => 'product_watermark_image']
                            );
                            $watermarkUploadImg->setAllowedExtensions(['jpeg', 'gif', 'png','jpg']);
                            $watermarkUploadImg->setAllowRenameFiles(true);
                            $watermarkImg = $watermarkUploadImg->save($watermarkImageTarget);
                            if ($watermarkImg['file']) {
                                $images['watermark_img'] = $watermarkImg['file'];
                                $parentPath = $watermarkImageTarget.$images['watermark_img'];
                                $value->setProductWatermarkImage($watermarkImg['file']);
                            }
                        } catch (\Exception $e) {
                            if ($e->getMessage() != 'The file was not uploaded.') {
                                $this->messageManager->addError($e->getMessage());
                            } else {
                                if (isset($images['watermark_img'])) {
                                    $value->setProductWatermarkImage($images['watermark_img']);
                                    //The name of the directory that we need to create.
                                    $directoryName = $watermarkImageTarget;
                                     
                                    //Check if the directory already exists.
                                    if(!is_dir($directoryName)){
                                        //Directory does not exist, so lets create it.
                                        mkdir($directoryName, 0777, true);
                                    }
                                    $file = $parentPath;
                                    $newfile = $watermarkImageTarget.$images['watermark_img'];
                                     
                                    if (!copy($file, $newfile)) {
                                        echo "failed to copy $file...\n";
                                    }else{
                                        echo "copied $file into $newfile\n";
                                    }
                                }
                            }
                        }

                        if (array_key_exists('country_pic', $fields)) {
                            $value->setCountryPic($fields['country_pic']);
                        }
                        $value->save();

                        if (array_key_exists('country_pic', $fields)) {
                            $value->setCountryPic($fields['country_pic']);
                        }
                        $value->setStoreId($storeId);
                        $value->save();
                        if ($isTranslated) {
                            if (isset($fields['mis_store_id'])) {
                                if ($fields['mis_store_id']) {
                                    break;
                                }
                            }
                        }
                    }
                    try {
                        if (!empty($errors)) {
                            foreach ($errors as $message) {
                                $this->messageManager->addError($message);
                            }
                        } else {
                            $vendorEmail = $this->_customerSession->getCustomer()->getEmail();
                            $vendorName = $this->_customerSession->getCustomer()->getName();
                            $sender = [
                                'name' => $vendorName,
                                'email' => $vendorEmail
                            ];

                            $scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
                            $emailHelper = $this->_objectManager->create('\Mangoit\TranslationSystem\Helper\Email');
                            $emailTemplate = $emailHelper->getTemplateId('marketplace/email/seller_profile_approval');
                            
                            $salesEmail = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $postObject = new \Magento\Framework\DataObject();
                            $postObject->setData(['name'=> $vendorName,'sellerId' => $sellerId]);
                            if (!$trustworthy) {
                                // $this->_transportBuilder->setTemplateIdentifier('marketplace_email_seller_profile_approval')->setTemplateOptions(
                                $this->_transportBuilder->setTemplateIdentifier($emailTemplate)->setTemplateOptions(
                                  [
                                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                  ]
                                )
                                ->setTemplateVars(['data' => $postObject])
                                ->setFrom($sender)
                                ->addTo($salesEmail);
                                $this->_transportBuilder->getTransport()->sendMessage();
                            }
                            $this->messageManager->addSuccess($this->_marketPlaceHelper->getProfileSavedMessage()
                            );
                        }

                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/editProfile',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('We can\'t save the customer.'));
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } else {
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

}
