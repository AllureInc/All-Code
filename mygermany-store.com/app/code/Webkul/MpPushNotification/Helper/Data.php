<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * MpPushNotification data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEND_URL_CHROME = 'https://android.googleapis.com/gcm/send';

    const SEND_URL_FIREFOX = 'https://updates.push.services.mozilla.com/wpush/v1/';

    const IMAGE_INDEX = 'logo';

    /**
     * \Psr\Log\LoggerInterface
     */
    protected $_logger;

     /**
      * \Magento\Customer\Model\Session
      */
    protected $_customerSession;

     /**
      * \Webkul\MpPushNotification\Model\Templates
      */
    protected $_templates;

    /**
     * \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;
    
     /**
      * \Magento\Framework\Encryption\EncryptorInterface
      */
    protected $enc;
    /**
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param \Magento\Customer\Model\Session                  $session
     * @param \Webkul\MpPushNoTimezoneInterface $localeDate,tification\Model\Templates       $templates
     * @param \Magento\Framework\Filesystem                    $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $enc,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpPushNotification\Model\Templates $templates,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\HTTP\Client\Curl $curl,
        TimezoneInterface $localeDate
    ) {
        $this->curl = $curl;
        $this->enc = $enc;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_logger = $context->getLogger();
        $this->_customerSession = $session;
        $this->_templates = $templates;
        $this->_filesystem = $filesystem;
        $this->_fileUploader = $fileUploaderFactory;
        $this->_date = $date;
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

     /**
      * Get notificationmessage for chrome browser.
      *
      * @param string $registrationId
      *
      * @throws \Magento\Framework\Exception\LocalizedException
      */
    public function sendToChrome($registrationId, $notification)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = [
            'data' => $notification,
            'to' => $registrationId,
        ];
        $headers = [
            'Authorization: key=' . $this->getServerKey(),
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * get seller id
     * @return int
     */
    public function getSellerId()
    {
        return $this->_customerSession->getCustomer()->getId();
    }

    /**
     * upload image
     * @return bool|string
     */
    public function uploadImage()
    {
        $fileId = self::IMAGE_INDEX;
        $uploadPath = $this->_filesystem
                            ->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('marketplace/mppushnotification/');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif','jpeg'];
        try {
            $uploader = $this->_fileUploader->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($allowedExtensions);
            $imageData = $uploader->validateFile();
            $name = $imageData['name'];
            $ext = explode('.', $name);
            $ext = strtolower(end($ext));
            $imageName = 'Logo-'.time().'.'.$ext;
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->save($uploadPath, $imageName);
        } catch (\Exception $e) {
            return false;
        }
        return $imageName;
    }

    /**
     * save template
     * @param  array $data
     * @return array
     */
    public function saveTemplate($data)
    {
        $result = [];
        $data['seller_id'] = $this->getSellerId();
        $data['created_at'] = $this->localeDate->date()->format('Y-m-d H:i:s');
        if (empty($data['logo'])) {
            unset($data['logo']);
        }
        $result['id'] = $this->_templates->addData($data)->save()->getId();
        if (empty($result['id'])) {
            $result['errorFlag'] = true;
        } else {
            $result['errorFlag'] = false;
        }
        return $result;
    }

    /**
     * validate form fields
     * @param  array $field
     * @return boolean
     */
    public function validateFormField($fields)
    {
        if (empty($fields['title']) || empty($fields['message']) || empty($fields['url']) || empty($fields['tags'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getSenderId()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'mppushnotification/mppushnotification/application_sender_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * @return string
     */
    public function getServerKey()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'mppushnotification/mppushnotification/application_server_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'mppushnotification/mppushnotification/application_public_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * @return string
     */
    public function getSecureUrl()
    {
        return $this->scopeConfig->getValue(
            'web/secure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getFCMConfig($value)
    {
        return $this->scopeConfig->getValue(
            'mppushnotification/mppushnotification/'.$value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getFCMConfigEncrypted($value)
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'mppushnotification/mppushnotification/'.$value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }
}
