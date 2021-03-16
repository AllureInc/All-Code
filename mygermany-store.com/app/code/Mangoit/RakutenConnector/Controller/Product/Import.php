<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Product;

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
        \Mangoit\RakutenConnector\Helper\Data $dataHelper,
        \Mangoit\RakutenConnector\Helper\ManageProductRawData $manageProductRawData,
        \Mangoit\RakutenConnector\Logger\Logger $logger
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
        $rktnClient = $this->dataHelper->getRktnClient();
        try {
            if ($rktnClient) {
                $response = $this->manageProductRawData->getFinalProductReport();
            } else {
                $response = ['error' => 'true','error_msg' => __('Rakuten Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('Product Import : '.$e->getMessage());
            $response = ['error' => 'true','error_msg' => $e->getMessage()];
        }
        return $resultJson->setData($response);
    }
}
