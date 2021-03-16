<?php
namespace Mangoit\Vendorcommission\Controller\Cron;

/**
* 
*/
class Turnovercron extends \Magento\Framework\App\Action\Action
{
    protected $objectmanager;
    protected $logger;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Psr\Log\LoggerInterface $logger)
    {

        $this->objectmanager = $objectmanager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        // die('i died');
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/turnovercron.log');
        $_logger = new \Zend\Log\Logger();
        $_logger->addWriter($writer);
        $_logger->info(__METHOD__);
        $data = ['seller_turnover'=> 0.000];

        $model = $this->objectmanager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
        $collection = $model->addFieldToFilter('seller_turnover', ['gt' => '0.00']);

        try {
            if (($collection->count()) > 0) {
                foreach ($collection as $collectionItem) {
                    if ($collectionItem->setSellerTurnover(0.0000)) {
                        $collectionItem->save(); 
                        $this->logger->debug(array('message'=> "Reset the turnover of seller_id ".$collectionItem->getSellerId()."."));    
                        $_logger->info("Reset the turnover of seller_id ".$collectionItem->getSellerId().".");   
                    }
                }
            } else {
                $this->logger->debug(array('message'=> "All seller's turnover are already reset to zere."));
                $_logger->info("All seller's turnover are already reset to zero.");
            }
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            $_logger->info($e->getMessage());
        }
        
    }
}