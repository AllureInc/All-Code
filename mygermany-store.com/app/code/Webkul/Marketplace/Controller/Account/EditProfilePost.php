<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Account EditprofilePost Controller.
 */
class EditProfilePost extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * File Uploader factory.
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

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
        \Webkul\Marketplace\Helper\Data $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->helper = $helper;
        parent::__construct(
            $context
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
                $errors = $this->validateprofiledata($fields);
                $sellerId = $this->helper->getCustomerId();
                $storeId = $this->helper->getCurrentStoreId();
                $img1 = '';
                $img2 = '';
                if (empty($errors)) {
                    $autoId = 0;
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
                    // echo'<pre>';print_r($fields);die('hi');
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
                    if ($trustworthy) {
                        $value->setIsProfileApproved(1);
                        $value->setProfileAdminNotification(0);
                    } else {
                        $value->setIsProfileApproved(0);
                        $value->setProfileAdminNotification(1);
                    }
                    $value->setMetaDescription($fields['meta_description']);

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
                            $value->setBannerPic($result['file']);
                        }
                    } catch (\Exception $e) {
                        if ($e->getMessage() != 'The file was not uploaded.') {
                            $this->messageManager->addError($e->getMessage());
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
                            $value->setLogoPic($resultLogo['file']);
                        }
                    } catch (\Exception $e) {
                        if ($e->getMessage() != 'The file was not uploaded.') {
                            $this->messageManager->addError($e->getMessage());
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
                            $salesEmail = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $postObject = new \Magento\Framework\DataObject();
                            $postObject->setData(['name'=> $vendorName,'sellerId' => $sellerId]);
                            if (!$trustworthy) {
                                $this->_transportBuilder->setTemplateIdentifier('seller_profile_approval')->setTemplateOptions(
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
                            $this->messageManager->addSuccess(
                                __('Profile information was successfully saved')
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

    protected function validateprofiledata(&$fields)
    {
        $errors = [];
        $data = [];
        foreach ($fields as $code => $value) {
            switch ($code) :
                case 'twitter_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Twitterid cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Facebookid cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'instagram_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Instagram ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'gplus_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Google Plus ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'youtube_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Youtube ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'vimeo_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Vimeo ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'pinterest_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Pinterest ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'moleskine_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Moleskine ID cannot contain space and special characters, allowed special carecters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'taxvat':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)
                    ) {
                        $errors[] = __('Tax/VAT Number cannot contain space and special characters');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'shop_title':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'contact_number':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_locality':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_keyword':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'shipping_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;

                case 'return_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'background_width':
                    if (trim($value) != '' &&
                        strlen($value) != 6 &&
                        substr($value, 0, 1) != '#'
                    ) {
                        $errors[] = __('Invalid Background Color');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
            endswitch;
        }

        return $errors;
    }

    protected function getSellerProfileFields($fields = [])
    {
        if (!isset($fields['tw_active'])) {
            $fields['tw_active'] = 0;
        }
        if (!isset($fields['fb_active'])) {
            $fields['fb_active'] = 0;
        }
        if (!isset($fields['gplus_active'])) {
            $fields['gplus_active'] = 0;
        }
        if (!isset($fields['youtube_active'])) {
            $fields['youtube_active'] = 0;
        }
        if (!isset($fields['vimeo_active'])) {
            $fields['vimeo_active'] = 0;
        }
        if (!isset($fields['instagram_active'])) {
            $fields['instagram_active'] = 0;
        }
        if (!isset($fields['pinterest_active'])) {
            $fields['pinterest_active'] = 0;
        }
        if (!isset($fields['moleskine_active'])) {
            $fields['moleskine_active'] = 0;
        }
        return $fields;
    }
}
