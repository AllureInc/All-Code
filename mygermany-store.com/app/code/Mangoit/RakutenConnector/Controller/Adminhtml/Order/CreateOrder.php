<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\RakutenConnector\Api\OrderMapRepositoryInterface;
use Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface;
use Mangoit\RakutenConnector\Controller\Adminhtml\Order;

class CreateOrder extends Order
{
    /**
     * @var \Mangoit\RakutenConnector\Model\Ordermap
     */
    private $orderMapRecord;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Order
     */
    private $orderData;

    /**
     * @var OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @var AmazonTempDataRepositoryInterface
     */
    private $rakutenTempDataRepo;

    /**
     * @param Context                                     $context
     * @param \Mangoit\RakutenConnector\Model\OrderMap $orderMapRecord
     * @param OrderMapRepositoryInterface                 $orderMapRepository
     * @param \Mangoit\RakutenConnector\Helper\Order   $orderData
     * @param AmazonTempDataRepositoryInterface           $rakutenTempDataRepo
     */
    public function __construct(
        Context $context,
        \Mangoit\RakutenConnector\Model\OrderMap $orderMapRecord,
        OrderMapRepositoryInterface $orderMapRepository,
        \Mangoit\RakutenConnector\Helper\Order $orderData,
        AmazonTempDataRepositoryInterface $rakutenTempDataRepo,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->orderMapRecord = $orderMapRecord;
        $this->orderData = $orderData;
        $this->orderMapRepository = $orderMapRepository;
        $this->rakutenTempDataRepo = $rakutenTempDataRepo;
        $this->helper = $helper;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $sellerId = $this->helper->getCustomerId($id);
        $tempData = $this->helper
              ->getTotalImported('order', $sellerId);
        if ($tempData) {
            $backendSession = $this->objectManager->get(
                '\Magento\Backend\Model\Session'
            );
            $backendSession->setAmzSession('start');
            $tempOrder = json_decode($tempData->getItemData(), true);
            $mapedOrder = $this->orderMapRepository
                        ->getByRktnOrderId($tempOrder['rktn_order_id']);

            if (!$this->helper->getRakutenAccountId($mapedOrder)) {
                //Create order in store as Rakuten
                $result = $this->orderData
                      ->createRakutenOrderAtMage($tempOrder);
                if (isset($result['order_id']) && $result['order_id']) {
                    $data = [
                            'rakuten_order_id' => $tempOrder['rktn_order_id'],
                            'mage_order_id' => $result['order_id'],
                            'status' => $tempOrder['order_status'],
                            'seller_id'   => $sellerId,
                            'purchase_date' => $tempOrder['purchase_date']
                          ];
                    $record = $this->orderMapRecord;
                    $record->setData($data)->save();
                }
            } else {
                $result = [
                    'error' => 1,
                    'msg' => __('Rakuten order ').$tempOrder['rktn_order_id'].
                            __(' already maped with store order #').$mapedOrder->getMageOrderId()
                ];
            }

            $tempData->delete();
            $backendSession->setAmzSession('');
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        } else {
            $data = $this->getRequest()->getParams();
            $total = (int) $data['count'] - (int) $data['skip'];
            $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Order(s) Imported.').'</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
            $result['msg'] = $msg;
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
