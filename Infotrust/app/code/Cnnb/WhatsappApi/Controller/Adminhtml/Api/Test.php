<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Controller For Mass action
 * For testing the API credentials
 */
namespace Cnnb\WhatsappApi\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Cnnb\WhatsappApi\Logger\Logger as CnnbLogger;

class Test extends \Magento\Backend\App\Action
{
    /**
     * @var $_helper;
     */
    protected $_helper;

    /**
     * @var $_messageManager;
     */
    protected $_messageManager;

    /**
     * @var $_logger;
     */
    protected $_logger;

    /**
     * @var $_resultJsonFactory;
     */
    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        \Cnnb\WhatsappApi\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultJsonFactory $resultJsonFactory,
        CnnbLogger $customLogger
    ) {
        parent::__construct($context);
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
        $this->_logger = $customLogger;
        $this->_resultJsonFactory = $resultJsonFactory;
    }
    
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $username = $this->_helper->getWhatsAppUsername();
        $password = $this->_helper->getWhatsAppPassword();
        $apiUrl = $this->_helper->getWhatsAppApiUrl();

        if (!$username && strlen($username) < 1) {
            $this->_messageManager->addError(__("Please check username"));
            $this->_logger->info('username is invalid');
            return $resultJson->setData(['result'=>false]);
        }
        $this->_logger->info('username is valid');

        if (!$password && strlen($password) < 1) {
            $this->_messageManager->addError(__("Please check password"));
            $this->_logger->info('password is invalid');
            return $resultJson->setData(['result'=>false]);
        }
        $this->_logger->info('password is valid');

        if (!$apiUrl && strlen($apiUrl) < 1) {
            $this->_messageManager->addError(__("Please check API URL"));
            $this->_logger->info('API-URL is invalid');
            return $resultJson->setData(['result'=>false]);
        }
        $this->_logger->info('API-URL is valid');
        
        $credentialData = json_encode(['username'=> $username, 'password'=> $password]);
        if ($this->_helper->runCurl($apiUrl, $credentialData)) {
            $this->_messageManager->addSuccess(__("Credentials are correct."));
            $this->_logger->info("Credentials are correct.");
            $this->_logger->info("## Result : ".json_encode(['result'=> true]));
            return $resultJson->setData(['result'=>true]);
        }
    }
}
