<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Mangoit\RakutenConnector\Helper\ManageOrderRawData;
use Mangoit\RakutenConnector\Controller\Adminhtml\Order;
use Mangoit\RakutenConnector\Helper;

class Import extends Order
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Mangoit\RakutenConnector\Helper\ManageOrderRawData
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
     * Rakuten order import controller.
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
                $this->helper->getRktnClient($params['id']);
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
