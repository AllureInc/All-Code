<?php


namespace Kerastase\Aramex\Controller\Adminhtml\Rma;


class Create  extends \Magento\Backend\App\Action
{

    const ORDER_STATUS_TO_TO_BE_RETURNED ='to_be_returned';
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;

    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Kerastase\Aramex\Logger\Logger $logger,
        \Kerastase\Aramex\Helper\Data $_helper,
        \Magento\Sales\Model\Order $order,
        \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog,
        \Magento\Framework\UrlInterface $urlBuilder


){
        parent::__construct($context);
        $this->logger = $logger;
        $this->_helper = $_helper;
        $this->order = $order;
        $this->orderLog = $orderLog;
        $this->_urlBuilder = $urlBuilder;
        $this->resultFactory = $context->getResultFactory();

    }

    public function  execute()
    {
        $this->logger->info('### Create RMA ###');

        try{
            $order_id = $this->getRequest()->getParam('order_id');
            $order = $this->order->load($order_id);
            if($order->getData()!=null){
                $this->_helper->generateReturnXMLFiles($order);
                $order->setStatus(SELF::ORDER_STATUS_TO_TO_BE_RETURNED);
                $comment = 'Create RMA from admin and xml file sent to Aramex';

                $this->logger->info('NEW ORDER STATUS ', array($order->getStatus()));

                $order->addStatusHistoryComment($comment, false);
                $order->save();
                /******** add Order history log ********/

                $historyDate = date('Y-m-d H:i:s');
                $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'order Confirmation','order Confirmation',$comment,$historyDate);
            }else{
                $this->logger->info('### Order does not exist ###');
            }
        }catch(\Exception $ex){
            $this->logger->info($ex);
        }
        /* Back to Order Edit Page */
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_urlBuilder->getUrl(
            'sales/order/view',
            ['order_id' => $order_id]
            );
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }


}