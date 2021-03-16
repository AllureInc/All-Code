<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\RakutenConnector\Api\OrderMapRepositoryInterface;
use Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface;

class CreateOrder extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Mangoit\RakutenConnector\Model\OrderMap $orderMapRecord,
        OrderMapRepositoryInterface $orderMapRepository,
        \Mangoit\RakutenConnector\Helper\Order $orderData,
        AmazonTempDataRepositoryInterface $rakutenTempDataRepo,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->customerSession = $customerSession;
        $this->orderMapRecord = $orderMapRecord;
        $this->orderData = $orderData;
        $this->orderMapRepository = $orderMapRepository;
        $this->rakutenTempDataRepo = $rakutenTempDataRepo;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $sellerId = $this->helper->getCustomerId();
        $tempData = $this->helper
                        ->getTotalImported('order', $sellerId);
                        // print_r($tempData);
                        // die('asdasdasdasd');
        $this->helper->getCoreSession()->setData('rktn_session', 'start');
        if ($tempData) {
            $tempOrder = json_decode($tempData->getItemData(), true);
            $mapedOrder = $this->orderMapRepository
                        ->getByRktnOrderId($tempOrder['rktn_order_id']);

// print_r($tempOrder);
// // var_dump($this->helper->getRakutenAccountId($mapedOrder));
// die;
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
            $this->helper->getCoreSession()->setData('rktn_session', '');
        } else {
            $data = $this->getRequest()->getParams();
            $total = (int) $data['count'] - (int) $data['skip'];
            $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Order(s) Imported.').'</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
            $result['msg'] = $msg;
        }
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }
}
