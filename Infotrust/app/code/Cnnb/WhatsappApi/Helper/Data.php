<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper CLass
 * For rendering data
 */


namespace Cnnb\WhatsappApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Cnnb\WhatsappApi\Model\Notification;
use Cnnb\WhatsappApi\Logger\Logger as CnnbLogger;
use Magento\Store\Model\StoreRepository;

class Data extends AbstractHelper
{
    const XML_PATH_INTERNATIONAL_TELEPHONE_INPUT_MODULE_ENABLED = 'internationaltelephoneinput/general/enabled';
    const XML_PATH_INTERNATIONAL_TELEPHONE_MULTISELECT_COUNTRIES_ALLOWED = 'internationaltelephoneinput/general/allow';
    const XML_PATH_PREFERED_COUNTRY = 'general/store_information/country_id';
    const XML_PATH_API_URI = 'cnnb_whatsappapi/smsgatways/api_url';
    const XML_PATH_USERNAME = 'cnnb_whatsappapi/smsgatways/username';
    const XML_PATH_PASSWORD = 'cnnb_whatsappapi/smsgatways/password';
    const XML_PATH_MESSAGE_TEMPLATE = 'cnnb_whatsappapi/orderplace/message_template_list';
    const XML_PATH_NUMBER_OF_MESSAGE = 'cnnb_whatsappapi/orderplace/no_of_message';

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @var $_authToken
     */
    protected $_authToken;

    /**
     * @var $messageManager
     */
    protected $_messageManager;

    /**
     * @var $notificationModel
     */
    protected $_notificationModel;

    /**
     * @var $storeRepository
     */
    protected $_storeRepository;

    public function __construct(
        Context $context,
        Notification $notificationModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CnnbLogger $customLogger,
        StoreRepository $storeRepository
    ) {
        parent::__construct($context);
        $this->_logger = $customLogger;
        $this->_messageManager = $messageManager;
        $this->_notificationModel = $notificationModel;
        $this->_storeRepository = $storeRepository;
    }

    /**
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->getConfig(self::XML_PATH_INTERNATIONAL_TELEPHONE_INPUT_MODULE_ENABLED);
    }

    /**
     * @return mixed
     */
    public function allowedCountries()
    {
        return $this->getConfig(self::XML_PATH_INTERNATIONAL_TELEPHONE_MULTISELECT_COUNTRIES_ALLOWED);
    }

    /**
     * @return mixed
     */
    public function preferedCountry()
    {
        return $this->getConfig(self::XML_PATH_PREFERED_COUNTRY);
    }

    /**
     * @return mixed
     */
    public function getWhatsAppUsername()
    {
        return $this->getConfig(self::XML_PATH_USERNAME);
    }

    /**
     * @return mixed
     */
    public function getWhatsAppPassword()
    {
        return $this->getConfig(self::XML_PATH_PASSWORD);
    }

    /**
     * @return mixed
     */
    public function getWhatsAppApiUrl()
    {
        return $this->getConfig(self::XML_PATH_API_URI);
    }

    /**
     * @return mixed
     */
    public function getWhatsAppMessageTemplate($store_id)
    {
        /*return $this->getConfig(self::XML_PATH_MESSAGE_TEMPLATE);*/
        return $this->scopeConfig->getValue(self::XML_PATH_MESSAGE_TEMPLATE, ScopeInterface::SCOPE_STORE, $store_id);
    }

    /**
     * @return mixed
     */
    public function getMessageLimit($store_id)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NUMBER_OF_MESSAGE, ScopeInterface::SCOPE_STORE, $store_id);
    }

    /**
     * @param $configPath
     * @return mixed
     */
    protected function getConfig($configPath)
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Prepare telephone field config according to the Magento default config
     * @param $addressType
     * @param string $method
     * @return array
     */
    public function telephoneFieldConfig($addressType, $method = '')
    {
        return  [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => $addressType . $method,
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'Cnnb_WhatsappApi/form/element/telephone',
                'tooltip' => [
                    'description' => 'For delivery questions.',
                    'tooltipTpl' => 'ui/form/element/helper/tooltip'
                ],
            ],
            'dataScope' => $addressType . $method . '.telephone',
            'dataScopePrefix' => $addressType . $method,
            'label' => __('Phone Number'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 120,
            'validation' => [
                "required-entry"    => true,
                "max_text_length"   => 255,
                "min_text_length"   => 1
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'focused' => false,
        ];
    }

    /**
     * Check run curl
     */
    public function runCurl($apiUrl, $credentialData)
    {
        try {
            $ch = curl_init();
            $contentType = "Content-Type: application/json";
            $contentLength = "Content-Lenght: ";
            curl_setopt($ch, CURLOPT_URL, $apiUrl.'/jwt/');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $credentialData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$contentType, $contentLength . strlen($credentialData)]);
            $result = curl_exec($ch);
            $data = json_decode($result, true);
            $this->_authToken = $data['token'];

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                curl_close($ch);
                return true;
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            $this->_logger->info("### Exception: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Function for getting authentication token
     * @return string
     */
    public function getAuthToken()
    {
        $username = $this->getWhatsAppUsername();
        $password = $this->getWhatsAppPassword();
        $apiUrl = $this->getWhatsAppApiUrl();
        $credentialData = json_encode(['username'=> $username, 'password'=> $password]);
        $curlResult = $this->runCurl($apiUrl, $credentialData);
        if ($curlResult == false) {
            return false;
        } else {
            return $this->_authToken;
        }
    }

    /**
     * Function for getting template lists
     * @return array
     */
    public function getMessageTemplateLists()
    {
        $authToken = $this->getAuthToken();
        $apiUrl = $this->getWhatsAppApiUrl();
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $apiUrl.'/templates/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    "Content-Type: application/json",
                    "Authorization: JWT ".$authToken
                ]
            );
            $result = curl_exec($ch);
            
            $data = json_decode($result, true);
            curl_close($ch);
            return $data;
            
        } catch (Exception $e) {
            $this->_logger->info("### Exception: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Function for sending whats app notifications
     */
    public function sendWhatsAppNotification($store_id, $api_params, $authToken)
    {
        $today = date('Y-m-d');
        $sent = false;
        $messageLimit = $this->getMessageLimit($store_id);

        /* If the message limit is not set from the admin then by default limit will be 10.*/
        if (!$messageLimit || $messageLimit == null) {
            $messageLimit = 10;
        }

        $model = $this->_notificationModel;
        $collection = $this->_notificationModel->getCollection();
        $collection->addFieldToFilter('created_at', ['like' => $today.'%']);
        $filteredResult = $collection->count();
        
        $template_uuid = $this->getWhatsAppMessageTemplate($store_id);
        $apiUrl = $this->getWhatsAppApiUrl();

        if ($template_uuid && strlen($template_uuid) > 4 && ($filteredResult < $messageLimit)) {
            $data['template_uuid'] = $template_uuid;
            $data['params'] = $api_params;

            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl.'/template_message/single/');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        "Content-Type: application/json",
                        "Authorization: JWT ".$authToken
                    ]
                );
                $result = curl_exec($ch);
               
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
                    $this->saveNotificationLog($api_params, $model);
                    $finalResult = ['result'=> true, 'code'=> 'success'];
                    curl_close($ch);
                    $sent = true;
                    $this->_logger->info(' Message has been sent ');
                    return $finalResult;
                } else {
                    $this->_logger->info(' Curl Error: '.curl_error($ch));
                    $finalResult = [
                        'result'=> false,
                        'code'=> 'curl_err',
                        'message'=> ' Curl Error: '.curl_error($ch)
                    ];
                    curl_close($ch);
                    return $finalResult;
                }
            } catch (Exception $e) {
                curl_close($ch);
                $finalResult = ['result'=> false, 'code'=> 'curl_err'];
                $this->_logger->info("### Exception: ".$e->getMessage());
                return $finalResult;
            }
        } else {
            if (!$filteredResult < $messageLimit) {
                $m1 = 'The message limit has been reached. If you want to send more 
                notifications then please update the limit from the 
                "Store-> Configuration -> CNNB -> WhatsApp Integration -> Order Notification
                Setting"';
                $this->_messageManager->addError(__($m1));
            } else {
                $m2 = 'Please select the message template from the 
                "Store-> Configuration -> CNNB -> WhatsApp Integration -> Order Notification Setting"';
                $this->_messageManager->addError(__($m2));
            }

            if ($sent == true) {
                $finalResult = ['result'=> true, 'code'=> 'validation_err'];
            } else {
                $finalResult = ['result'=> false, 'code'=> 'validation_err'];
            }

            return $finalResult;
        }
    }

    public function saveNotificationLog($api_params, $model)
    {
        $model->setData(
            [
                'order_id'=> ''.$api_params['$2'],
                'customer_name'=> ''.$api_params['name'],
                'phone_number'=> ''.$api_params['phone_number'],
                'notification_status'=> true
            ]
        );
        $model->save();
    }

    /**
     * Get all stores
     */
    public function getAllStoreIds(){

        $stores = $this->_storeRepository->getList();
        $storeList = array();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            $storeList[] = $storeId;
        }
        return $storeList;
    }
}
