<?php
namespace Mangoit\Vendorcommission\Cron;

/**
* 
*/
class Turnovercron 
{
    protected $objectmanager;
    protected $logger;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Psr\Log\LoggerInterface $logger)
    {
        $this->objectmanager = $objectmanager;
        $this->logger = $logger;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/turnovercron.log');
        $_logger = new \Zend\Log\Logger();
        $_logger->addWriter($writer);
        $_logger->info(__METHOD__);


        $model = $this->objectmanager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
        $condition = "seller_turnover > 0";
        $status = 0;
        $this->logger->debug('Turn over Cron is running');
        try {
            $model->setTableRecords( $condition, ['seller_turnover' => $status]);            
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}