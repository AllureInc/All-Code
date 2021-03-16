<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpAmazonConnector\Api\OrderMapRepositoryInterface;
use Webkul\MpAmazonConnector\Api\AmazonTempDataRepositoryInterface;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Order;

class CreateOrder extends Order
{
    /**
     * @var \Webkul\MpAmazonConnector\Model\Ordermap
     */
    private $orderMapRecord;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Order
     */
    private $orderData;

    /**
     * @var OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @var AmazonTempDataRepositoryInterface
     */
    private $amazonTempDataRepo;

    /**
     * @param Context                                     $context
     * @param \Webkul\MpAmazonConnector\Model\OrderMap $orderMapRecord
     * @param OrderMapRepositoryInterface                 $orderMapRepository
     * @param \Webkul\MpAmazonConnector\Helper\Order   $orderData
     * @param AmazonTempDataRepositoryInterface           $amazonTempDataRepo
     */
    public function __construct(
        Context $context,
        \Webkul\MpAmazonConnector\Model\OrderMap $orderMapRecord,
        OrderMapRepositoryInterface $orderMapRepository,
        \Webkul\MpAmazonConnector\Helper\Order $orderData,
        AmazonTempDataRepositoryInterface $amazonTempDataRepo,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->orderMapRecord = $orderMapRecord;
        $this->orderData = $orderData;
        $this->orderMapRepository = $orderMapRepository;
        $this->amazonTempDataRepo = $amazonTempDataRepo;
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
                        ->getByAmzOrderId($tempOrder['amz_order_id']);

            if (!$this->helper->getAmazonAccountId($mapedOrder)) {
                //Create order in store as Amazon
                $result = $this->orderData
                      ->createAmazonOrderAtMage($tempOrder);
                if (isset($result['order_id']) && $result['order_id']) {
                    $data = [
                            'amazon_order_id' => $tempOrder['amz_order_id'],
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
                    'msg' => __('Amazon order ').$tempOrder['amz_order_id'].
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
