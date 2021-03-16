<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpAmazonConnector\Helper\ManageOrderRawData;
use Webkul\MpAmazonConnector\Helper\Data;

class Import extends Action
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
        JsonFactory $resultJsonFactory,
        ManageOrderRawData $manageOrderRawData,
        Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->manageOrderRawData = $manageOrderRawData;
        $this->helper = $helper;
        $this->_customerSession = $customerSession;
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

        if (!$this->_customerSession->authenticate($loginUrl)) {
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
        $response = null;
        $resultJson = $this->resultJsonFactory->create();
        try {
            $params = $this->getRequest()->getParams();
            if (isset($params['order_from']) && $params['order_from']) {
                $this->helper->getAmzClient();
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
