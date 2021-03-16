<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper CLass
 * For adding the comment in the order history after sending whatsapp notification
 */
namespace Cnnb\WhatsappApi\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Cnnb\WhatsappApi\Logger\Logger as CnnbLogger;

class OrderHistoryHelper extends AbstractHelper
{
    const XML_PATH_ORDER_COMMENT = 'cnnb_whatsappapi/orderplace/order_comment';

    /** @var LoggerInterface */
    protected $logger;

    /** @var OrderStatusHistoryRepositoryInterface */
    protected $orderStatusRepository;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var ScopeInterface */
    protected $scopeConfig;


    public function __construct(
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        CnnbLogger $cnnbLogger
    ) {
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $cnnbLogger;
    }

    /**
     * add comment to the order history
     *
     * @param int $orderId
     * @return OrderStatusHistoryInterface|null
     */
    public function addCommentToOrder(int $orderId, $phone_number)
    {
        $order = null;

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->error($exception->getMessage());
        }

        $orderHistory = null;
        if ($order) {
            $comment = $order->addStatusHistoryComment(__(sprintf($this->getOrderComment(), $phone_number)));
            try {
                $orderHistory = $this->orderStatusRepository->save($comment);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
        return ($orderHistory) ? true : false;
    }

    public function getOrderComment($store_id = 0)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_COMMENT, ScopeInterface::SCOPE_STORE, $store_id);
    }
}
