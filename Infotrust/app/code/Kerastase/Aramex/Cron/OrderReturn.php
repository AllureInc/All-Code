<?php


namespace Kerastase\Aramex\Cron;


use Magento\Framework\Exception\NoSuchEntityException;

class OrderReturn
{

    const ORDER_STATUS_TO_BE_RETURNED = 'to_be_returned';
    const ORDER_STATUS_RETURNED = 'returned';



    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    private $helper;
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    private $_convertOrder;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $trackFactory;

    public function __construct(\Kerastase\Aramex\Helper\Data $helper,
                                \Kerastase\Aramex\Logger\Logger $logger,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                \Magento\Sales\Model\Order $order,
                                \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog,
                                \Magento\Sales\Model\Convert\Order $convertOrder,
                                 \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory

    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->order = $order;
        $this->orderLog = $orderLog;
        $this->_convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
    }

    public function execute()
    {

        try{
            $this->logger->info('############################## START ORDER RETURN CRON ####################################');
            $pathToFiles = $this->helper->getOrderReturnAramexFolder();
            $failedFilesPath = $this->helper->getFaildFolderPath();
            $successPathFile = $this->helper->getSuccessFolderPath();
            $files = $this->helper->getAllFiles($pathToFiles);

            foreach ($files as $file) {
                $this->logger->info('### ORDER RETURN _ READING DATA FROM  '.$pathToFiles.'/'.$file);

                /*************** Load Order Informations ***************************/
                $AramexOrder = $this->helper->readXMLFile($pathToFiles.'/'.$file, 'ADVANCEDSHIPPINGNOTICECONF');

                if($AramexOrder['MASTERREF']){
                try{
                    /************** Get Invoice data by invoice id in xml ********/
                    $orders  = $this->collectionFactory->create();
                    $order = $orders->addFieldToFilter('increment_id',$AramexOrder['MASTERREF'])
                        ->getFirstItem();

                   $orderedItems = $AramexOrder["LINEITEM"];

                    $this->logger->info('ORDER RETURN - ARAMEX ORDERED ITEMS ',$orderedItems);

                    if($order->getData()!= null){
                        $this->logger->info('LOADED ORDER DATA',array($order->getData()));
                        $comment = '';
                        if($order->getStatus() == SELF::ORDER_STATUS_TO_BE_RETURNED){

                            $this->logger->info('LOADED ORDER ITEMS ORDERED',array($order->getItems()));
                            $qty_toCheck = 'QTYEXPECTED';
                            $difference = $this->helper->checkOrderedItems($order->getItems(),$orderedItems,$qty_toCheck);
                            if(count($difference) == 0){

                                $order->setData('status',SELF::ORDER_STATUS_RETURNED);
                                $comment = '#Order returned from '.$AramexOrder['ARAMEXREF'];

                                $this->logger->info('NEW ORDER STATUS ',array($order->getStatus()));
                                rename($pathToFiles.'/'.$file, $successPathFile.'/'.$file);
                            }else{

                                $this->logger->info('QUANTITIES DOES NOT MATCH  ');
                                $comment = '#Error occured when trying to update order status  # Quantities received from Aramex does not match';
                                $this->helper->sendNotmatchingQuantitiesToAdmin($difference)  ;                          }
                        }
                        else{
                            rename($pathToFiles.'/'.$file, $failedFilesPath.'/'.$file);
                           // $comment = '#Error in Order shipment Cron Order status is '.$order->getData('status').' It does not match with Aramex received Status #'.$AramexOrder['STATUS'];
                        }
                        $history = $order->addStatusHistoryComment($comment, false);
                        $history->setIsCustomerNotified(false);
                        $order->save();
                        /******** add Order history log ********/
                        $historyDate = date('Y-m-d H:i:s');
                        $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'order Return','order Return',$comment,$historyDate);

                    }else{
                        $this->logger->error('ORDER DOES NOT EXIST WITH INCREMENT ID '.$AramexOrder['MASTERREF']);
                    }

                }catch (NoSuchEntityException $ex){  throw $ex;}
            }
            }
        }catch (\Exception $exception){
            $this->logger->err($exception->getMessage());
        }
        $this->logger->info('############################## END ORDER RETURN CRON ####################################');


    }
}