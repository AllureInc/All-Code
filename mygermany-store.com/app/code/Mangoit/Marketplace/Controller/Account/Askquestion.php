<?php
namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Askquestion extends \Webkul\Marketplace\Controller\Account\Askquestion
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;


    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var MpEmailHelper
     */
    protected $mpEmailHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param MarketplaceHelper $marketplaceHelper
     * @param MpEmailHelper $mpEmailHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        MarketplaceHelper $marketplaceHelper,
        MpEmailHelper $mpEmailHelper,
        JsonHelper $jsonHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $customerSession, $marketplaceHelper, $mpEmailHelper, $jsonHelper);
    }

    /**
     * Ask Query to seller action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        $sellerName = $this->_customerSession->getCustomer()->getName();
        $sellerEmail = $this->_customerSession->getCustomer()->getEmail();

        $adminStoremail = $helper->getAdminEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
        /*$adminUsername = 'Admin';*/
        $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

        $emailTemplateVariables = [];
        $senderInfo = [];
        $receiverInfo = [];
        $emailTemplateVariables['myvar1'] = $adminUsername;
        $emailTemplateVariables['myvar2'] = $sellerName;
        $emailTemplateVariables['subject'] = isset($data['subject']) ? $data['subject'] : '';
        $emailTemplateVariables['myvar3'] = isset($data['ask']) ? $data['ask'] : '';
        $senderInfo = [
            'name' => $sellerName,
            'email' => $sellerEmail,
        ];
        $sellerStoreId = $this->_customerSession->getCustomer()->getStoreId();
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $this->_objectManager
        ->create('Webkul\Marketplace\Helper\Email')
        ->askQueryAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo, $sellerStoreId);
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
            ->jsonEncode('true')
        );
    }
}
