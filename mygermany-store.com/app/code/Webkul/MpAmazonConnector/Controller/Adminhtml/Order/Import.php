<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpAmazonConnector\Helper\ManageOrderRawData;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Order;
use Webkul\MpAmazonConnector\Helper;

class Import extends Order
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\ManageOrderRawData
     */
    private $manageOrderRawData;

    /**
     * @param Context            $context
     * @param JsonFactory        $resultJsonFactory
     * @param ManageOrderRawData $manageOrderRawData
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ManageOrderRawData $manageOrderRawData,
        Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->helper = $helper;
    }

    /**
     * Amazon order import controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $response = null;
        $resultJson = $this->resultJsonFactory->create();
        try {
            $params = $this->getRequest()->getParams();
            if (isset($params['isAjax']) && $params['isAjax']) {
                $this->helper->getAmzClient($params['id']);
                $finalReport = $this->manageOrderRawData
                        ->getFinalOrderReport(
                            $params
                        );
    
                $response = [
                    'data' => $finalReport['data'],
                    'notification'=>$finalReport['notification'],
                    'error_msg' => $finalReport['error_msg'],
                    'next_token'=>$finalReport['next_token']
                ];
            } else {
                $response = ['data' => '','error_msg' => __('Invalid parameters.')];
            }
        } catch (\Exception $e) {
            $response = ['data' => '','error_msg' => $e->getMessage()];
        }
        return $resultJson->setData($response);
    }
}
