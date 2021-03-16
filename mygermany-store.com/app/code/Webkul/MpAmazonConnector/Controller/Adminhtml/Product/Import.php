<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

class Import extends Product
{
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ManageProductRawData
     */
    protected $_manageProductRawData;

    /**
     * @param Context              $context
     * @param JsonFactory          $resultJsonFactory
     * @param AmazonGlobal         $amazonGlobal
     * @param ManageProductRawData $manageProductRawData
     * @param Amazonmws            $amazonmws
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Webkul\MpAmazonConnector\Helper\Data $dataHelper,
        \Webkul\MpAmazonConnector\Helper\ManageProductRawData $manageProductRawData,
        \Webkul\MpAmazonConnector\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->manageProductRawData = $manageProductRawData;
        ;
    }

    /**
     * Amazon  product import Controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        $amzClient = $this->dataHelper->getAmzClient($id);
        try {
            if ($id && $amzClient) {
                $response = $this->manageProductRawData->getFinalProductReport();
            } else {
                $response = ['error' => 'true','error_msg' => __('Amazon Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('backend Product Import : '.$e->getMessage());
            $response = ['error' => 'true','error_msg' => __('Something went wrong, Please check log.')];
        }
        return $resultJson->setData($response);
    }
}
