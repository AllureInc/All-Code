<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Import extends \Magento\Customer\Controller\AbstractAccount
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
        JsonFactory $resultJsonFactory,
        \Webkul\MpAmazonConnector\Helper\Data $dataHelper,
        \Webkul\MpAmazonConnector\Helper\ManageProductRawData $manageProductRawData,
        \Webkul\MpAmazonConnector\Logger\Logger $logger
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->manageProductRawData = $manageProductRawData;
        parent::__construct($context);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $amzClient = $this->dataHelper->getAmzClient();
        try {
            if ($amzClient) {
                $response = $this->manageProductRawData->getFinalProductReport();
            } else {
                $response = ['error' => 'true','error_msg' => __('Amazon Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('Product Import : '.$e->getMessage());
            $response = ['error' => 'true','error_msg' => $e->getMessage()];
        }
        return $resultJson->setData($response);
    }
}
