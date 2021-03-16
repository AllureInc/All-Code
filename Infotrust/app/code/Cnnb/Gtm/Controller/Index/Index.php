<?php
namespace Cnnb\Gtm\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_block;
    protected $resultFactory;
    protected $_logger;
    protected $_session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cnnb\Gtm\Block\DataLayer $block,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->_block = $block;
        $this->_session = $session;
        $this->_logger = $this->getZendLogger();
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $eventCall = '';
        if ($this->getRequest()->getParam('event_call') !== null) {
            $eventCall = $this->getRequest()->getParam('event_call');
        }

        if ($eventCall == 2) {
            $data = $this->_session->getUpdatedCartDataLayer();
            $this->_logger->info('----- Cnnb\Gtm\Controller\Index\Index ------');
            $this->_logger->info('--- Upadate the Cart ----');
            $this->_logger->info(print_r($data, true));
            $this->_session->unsCartDataLayer();
            return $resultJson->setData($data);
        } elseif($eventCall == 1) {
            $data = $this->_session->getRemoveCartDataLayer();
            $this->_logger->info('----- Cnnb\Gtm\Controller\Index\Index ------');
            $this->_logger->info('--- Remove from the Cart ----');
            $this->_logger->info(print_r($data, true));
            $this->_session->unsRemoveCartDataLayer();
            return $resultJson->setData($data);
        } else {
            $productType = $this->getRequest()->getParam('product_type');
            $productId = $this->getRequest()->getParam('product_id');
            $this->_logger->info('----- Cnnb\Gtm\Controller\Index\Index ------');
            $this->_logger->info(' ## Event Call : '.$eventCall);
            $this->_logger->info(' ## productType : '.$productType);
            $this->_logger->info(' ## productId : '.$productId);
            $resultJson->setData($this->_block->getCartDetails($eventCall, $productType, $productId));
        }
        return $resultJson;
    }

     /**
     * Function for logger
     */
    public function getZendLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gtm_check_controller.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}
