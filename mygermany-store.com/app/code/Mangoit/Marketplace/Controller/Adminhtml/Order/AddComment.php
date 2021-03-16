<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Controller\Adminhtml\Order;
// namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Order\AddComment
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        /*echo "<pre>";
        print_r($postData);
        die();*/
        $order = $this->_initOrder();
        // print_r($order->debug());
        // die();
        if ($order) {
            $order_id = $order->getEntityId();
            $order_increment_id = $order->getIncrementId();
            // $customer_email = $order->getCustomerEmail();
            try {
                $data = $this->getRequest()->getPost('history');

                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                if ( ($data['status'] == 'return_by_mygermany') || ($data['status'] == 'return_by_customer') ) {
                    $marketplaceHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\Data');
                    $marketplaceHelper->checkOrderDetailsForVendor($order_id, $data['status']);
                    // die();
                }

                if ($data['status'] == 'processing' || $data['status'] == 'received') {
                    $marketplaceHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\NotifySellerForProcessOrder');
                    $marketplaceHelper->notifySeller($order_id, $data['status']);
                    // die();
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));
                
                if ($data['status'] == 'processing') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'canceled') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'canceled_by_customer') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'canceled_by_vendor') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'closed') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'pending') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'processing') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'received') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'return_by_customer') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'return_by_mygermany') {
                    $order->setState($data['status']);
                } elseif ($data['status'] == 'sent_to_mygermany') {
                    $order->setState($data['status']);
                }

                /*print_r($order->debug());
                die();*/

                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                $orderCommentSender->send($order, $notify, $comment);

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                print_r($e->getMessage());
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
