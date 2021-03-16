<?php
/**
 * Created by PhpStorm.
 * User: faten
 * Time: 02:20 م
 */

namespace Kerastase\Aramex\Cron;

use  Kerastase\Aramex\Helper\Data;
use   \Magento\Framework\App\Config\ScopeConfigInterface  as ScopeConfigInterface;
class ChangeStatus
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';

    protected $logger;

    protected $salesOrderCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        \Kerastase\Aramex\Logger\Logger $logger,
        Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    }
    /*
     * @return void
     */

    public function execute()
    {
        $this->logger->info('############################## START COD AUTOAPPROVAL CRON ####################################');

        $orders=$this->helper->getCodOrders();
        $this->logger->info(__CLASS__.'::'.__METHOD__.' : '.'COD ORDERS ', array($orders) );

        $orderStatus=array();

        foreach ($orders as $order )
        {
            /* Check if customer has already an completed or delivered orders and he has no orders last 24 hours*/
            
           if (($this->helper->CustumerHascompletedOrder($order->getCustomerId()) == true) ) {

                $orderData = $order->load($order->getId());
                $orderData->setStatus(self::STATUS_PROCESSING);
                $orderData->save();
               
               $this->logger->info('AUTOAPPROVAL CRON:: Order Status changed to processing');

                array_push($orderStatus,array("id"=>$order->getIncrementId(),"status"=>'Approved'));

         } else {

               array_push($orderStatus,array("id"=>$order->getIncrementId(),"status"=>'Not Approved'));

                $this->logger->addWarning('AUTOAPPROVAL CRON:: An error has occured status not changed');

           }
        }

        /* Here we prepare data for our email  */
        /* Receiver Detail  */
        $receiverInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');

        /* Sender Detail  */
        $senderInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');

        $emailTempVariables = $orderStatus;

        /* We write send mail function in helper because if we want to
           use same in other action then we can call it directly from helper */
        $this->SendMailToAdmin( $emailTempVariables, $senderInfo, $receiverInfo);

        $this->logger->info('############################## END COD AUTOAPPROVAL CRON ####################################');
    }

    /**
     * @param $emailTemplateVariables
     * @param $emailFrom
     * @param $emailTo
     */
    public function SendMailToAdmin ($emailTemplateVariables, $emailFrom, $emailTo)
    {

        $subject = 'COD Orders status';
        $message = '';
        $message .= '<html><body><table><th>Order Increment Id</th><th>Status </th><tbody>';

        foreach ($emailTemplateVariables as $var) {
            $message .= '<tr><td>' . $var['id'] . '</td><td>' . $var['status'] . '</td></tr><tr>';
        }

        $message .= '</tbody></table></body></html>';

        $headers = 'From: ' . $emailFrom . '' . "\r\n" .
            'Reply-To: ' . $emailTo . '' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($emailTo, $subject, $message, $headers);
    }
}