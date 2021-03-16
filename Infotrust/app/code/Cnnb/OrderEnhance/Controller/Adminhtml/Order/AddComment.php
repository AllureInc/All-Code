<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderEnhance
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderEnhance\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Psr\Log\LoggerInterface;
use Cnnb\OrderEnhance\Helper\Data;

/**
 * Class AddComment
 */
class AddComment extends \Magento\Sales\Controller\Adminhtml\Order\AddComment
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * ACL resource needed to send comment email notification
     */
    const ADMIN_SALES_EMAIL_RESOURCE = 'Magento_Sales::emails';

    /**
     * Logger
     */
    protected $logger;


    /**
     * Helper
     */
    protected $helper;

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $this->logger = $this->_objectManager->create(LoggerInterface::class);
        $this->helper = $this->_objectManager->create(Data::class);
        $this->logger->info('------'.__CLASS__.'------');
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The comment is missing. Enter and try again.')
                    );
                }

                $notify = $data['is_customer_notified'] ?? false;
                $visible = $data['is_visible_on_front'] ?? false;

                if ($notify && !$this->_authorization->isAllowed(self::ADMIN_SALES_EMAIL_RESOURCE)) {
                    $notify = false;
                }

                $data['created_by'] = $this->helper->getLoggedInAdminId();

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                
                if ($this->helper->isModuleEnable()) {
                    $history->setCreatedBy($data['created_by']);
                    $order->setCreatedBy($data['created_by']);
                }

                $history->save();

                $comment = trim(strip_tags($data['comment']));
                $order->save();
                $this->logger->info('Order '.$order->getIncrementId().' has been saved with '.$data['status'].' status and comment : '.$data['comment'].' Saved By: '.$this->helper->getLoggedInAdminName());
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                $orderCommentSender->send($order, $notify, $comment);
                $this->logger->info('');
                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
