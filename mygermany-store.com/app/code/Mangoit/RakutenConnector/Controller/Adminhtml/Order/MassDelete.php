<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mangoit\RakutenConnector\Api\OrderMapRepositoryInterface;
use Mangoit\RakutenConnector\Controller\Adminhtml\Order;

class MassDelete extends Order
{
    /**
     * OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param Context                     $context
     * @param OrdermapRepositoryInterface $orderMapRepository
     */
    public function __construct(
        Context $context,
        OrderMapRepositoryInterface $orderMapRepository,
        \Magento\Sales\Model\Order $order,
        \Mangoit\RakutenConnector\Helper\Data $helper
    ) {
        $this->orderMapRepository = $orderMapRepository;
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $sellerId = $this->helper->getCustomerId($params['id']);
        $orderColl = $this->orderMapRepository
                    ->getByIds($params['orderEntityIds'], $sellerId);

        $orderDeleted = 0;
        $orderIds = [];
        foreach ($orderColl->getItems() as $orderMap) {
            $orderIds[] =  $orderMap->getMageOrderId();
            $orderMap->delete();
            ++$orderDeleted;
        }
        $orders = $this->order->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$orderIds]);

        foreach ($orders as $order) {
            $order->delete();
        }

        $this->messageManager->addSuccess(
            __("A total of %1 record(s) have been deleted.", $orderDeleted)
        );

        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                'id'=>$params['id'],
                'active_tab' => 'order_sync'
            ]
        );
    }
}
