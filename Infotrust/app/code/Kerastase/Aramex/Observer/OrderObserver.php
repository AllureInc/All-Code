<?php
namespace Kerastase\Aramex\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class OrderObserver implements ObserverInterface
{
    protected $logger;
    protected $orderRepository;

    const COMPLETE_STATE = 'complete';
    const COMPLETE_STATUS = 'complete';

    const CLOSED_STATE = 'closed';
    const CLOSED_STATUS = 'closed';

    const PROCESSING_STATE = 'processing';
    const DELIVERED_STATUS = 'processing_delivered';

    public function __construct(
        \Kerastase\Aramex\Logger\Logger $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $incrementId = $order->getIncrementId();
        $statuscode = $order->getStatus();
        $statecode = $order->getState();


        $originalStatuscode = "";
        $originalStatecode = "";

        $originalOrder = $order->getOrigData();
        if($originalOrder != null)
        {
            $originalStatuscode = $originalOrder['status'];
            $originalStatecode = $originalOrder['state'];
            if(($statecode == self::COMPLETE_STATE && $statuscode == self::COMPLETE_STATUS) || ($statecode == self::CLOSED_STATE && $statuscode == self::CLOSED_STATUS))
            {
                if($originalStatecode != self::PROCESSING_STATE || $originalStatuscode != self::DELIVERED_STATUS) // || $order->getData('base_total_paid') != $order->getData('base_grand_total'))
                {
                    $order->setState($originalOrder['state']);
                    $order->setStatus($originalOrder['status']);
                }
                else {
                    $order->setState(self::PROCESSING_STATE);
                    $order->setStatus(self::DELIVERED_STATUS);
                }

                $this->logger->info(__METHOD__." ==> $statecode : $statuscode changed to $originalStatecode : $originalStatuscode");

                $statuscode = $order->getStatus();
                $statecode = $order->getState();
            }
        }
        
        
        $this->logger->info(__METHOD__." ==> $originalStatecode : $originalStatuscode >> $statecode : $statuscode");

        
    }
}
