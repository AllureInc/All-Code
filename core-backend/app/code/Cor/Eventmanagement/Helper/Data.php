<?php
namespace Cor\Eventmanagement\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) 
    {
        parent::__construct($context);
    }

    /*
    * Method for get role id of artist
    *
    */
    public function getRoleId() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $roleModel = $objectManager->create('Magento\Authorization\Model\Role');
        $roleModel->load('Artist', 'role_name');
        $roleId = $roleModel->getRoleId();
        return $roleId;
    }

    /*
    * Method to send mail to artist associated with event and admin
    *
    */
    public function sendMailOnEventClose($recieverType, $recieverId = '') {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        if ($recieverType == 'admin') {
            $email = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $emailTemplate = 1;
        } else if($recieverType == 'artist' && !empty($recieverId)){
            $artistModel = $objectManager->create('Cor\Artist\Model\Artist')->load($recieverId, 'artist_id');
            $email = $artistModel['email_id'];
            $emailTemplate = 2;
        }

        $this->sendMail($email, $emailTemplate);
    }

    /*
    * Method to send mail to artist associated with event and admin
    *
    */
    public function sendMail($reciever, $templateId) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $transportBuilder = $objectManager->create('\Magento\Framework\Mail\Template\TransportBuilder');
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface'); 
        //Name and Email Address of Sales Representative.
        $salesName = $scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $salesEmail = $scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sender = [
           'name' => $salesName,
           'email' => $salesEmail,
        ];

        $transport = $transportBuilder
        ->setTemplateIdentifier($templateId)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ]
        )
        ->setTemplateVars(['data' => 'data'])
        ->setFrom($sender)
        ->addTo($reciever)
        ->getTransport();
        $transport->sendMessage();
    }
}