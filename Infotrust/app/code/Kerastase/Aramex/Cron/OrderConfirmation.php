<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 12:09 PM
 */

namespace Kerastase\Aramex\Cron;
use Magento\Framework\Exception\NoSuchEntityException;
class OrderConfirmation {


    const SUBMITTED_TO_WAREHOUSE ="submited_to_warehouse";
    /*
        * Aramex order status
        */
    const ARAMEX_ORDER_STATUS_ALLOCATED = 'processing_allocated';
    const ARAMEX_ORDER_STATUS_RELEASED = 'released';


    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    private $helper;
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
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

    public function __construct(\Kerastase\Aramex\Helper\Data $helper,
                                \Kerastase\Aramex\Logger\Logger $logger,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                \Magento\Sales\Model\Order $order,
                                \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog
)
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->order = $order;
        $this->orderLog = $orderLog;
    }

    public function execute()
    {

        try {
            $this->logger->info('#################################### START ORDER CONFIRMATION CRON ####################################');

            /************************ Load Order Informations ***************************/
            $pathToFiles = $this->helper->getOrderConfirmationAramexFolder();
            $failedFilesPath = $this->helper->getFaildFolderPath();
            $successPathFile = $this->helper->getSuccessFolderPath();
            $files = $this->helper->getAllFiles($pathToFiles);

            foreach ($files as $file) {

                $AramexOrder = $this->helper->readXMLFile($pathToFiles.'/'.$file, 'SHIPMENTORDERCONF');

                if ($AramexOrder['INVOICENB']) {
                    $this->logger->info('ORDER CONFIRMATION CRON:: ORDER INCREMENT ID IN XML FILE IS '.$AramexOrder['INVOICENB']);
                    try {
                        /************** Get Invoice data by invoice id in xml ********/
                        $orders = $this->collectionFactory->create();
                        $order = $orders->addFieldToFilter('increment_id', $AramexOrder['INVOICENB'])
                            ->getFirstItem();

                        if ($order->getData() ) {
                            $comment='';
                            $this->logger->info('ORDER CONFIRMATION CRON :: LOADED ORDER DATA', array($order->getData()));

                            if ($order->getStatus() == SELF::SUBMITTED_TO_WAREHOUSE) {
                                if ($AramexOrder['STATUS'] == SELF::ARAMEX_ORDER_STATUS_ALLOCATED || $AramexOrder['STATUS'] == SELF::ARAMEX_ORDER_STATUS_RELEASED) {
                                    /** check allocated items matching quantities**/
                                    $orderedItems = $AramexOrder["LINEITEM"];
                                    $qty_toCheck = 'QTYALLOCATED';
                                    $difference = $this->helper->checkOrderedItems($order->getItems(), $orderedItems, $qty_toCheck);

                                    if (count($difference) == 0) {
                                        $order->setStatus(SELF::ALLOCATED);
                                        $comment = 'Success Updating status from Aramex ';
                                        $this->logger->addDebug('ORDER CONFIRMATION CRON:: NEW ORDER STATUS ', array($order->getStatus()));
                                        rename($pathToFiles.'/'.$file, $successPathFile.'/'.$file);
                                    } else {
                                        $this->logger->info('ORDER CONFIRMATION CRON:: STATUS NOT CHANGED QUANTITIES DOES NOT MATCH ', array($order->getStatus()));
                                        $comment = 'Error occured when trying to update order status  # Quantities received from Aramex does not match';
                                        $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference));
                                    }

                                }

                            }else{
                                rename($pathToFiles.'/'.$file, $failedFilesPath.'/'.$file);
                                $comment = 'Error in Order confirmation Cron Order status is '.$order->getData('status').' It does not match with Aramex received Status #'.$AramexOrder['STATUS'];
                                $this->logger->info('#Error in Order shipment Cron Order status is '.$order->getData('status').' It does not match with Aramex received Status #'.$AramexOrder['STATUS']);

                            }
                            $order->addStatusHistoryComment($comment, false);
                            $order->save();
                            /******** add Order history log ********/
                            $historyDate = date('Y-m-d H:i:s');
                            $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'order Confirmation','order Confirmation',$comment,$historyDate);

                        } else {
                            $this->logger->error('ORDER CONFIRMATION CRON:: ORDER DOES NOT EXIST WITH INCREMENT ID ' . $AramexOrder['INVOICENB']);
                        }

                    } catch (NoSuchEntityException $ex) {
                        throw $ex;
                    }
                }

        }
        }catch (\Exception $exception){
            $this->logger->err($exception->getMessage());
        }
        $this->logger->addDebug('############################## END ORDER CONFIRMATION CRON ####################################');


    }


}
