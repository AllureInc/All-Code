<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Mangoit\RakutenConnector\Controller\Adminhtml\Product;

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
        \Mangoit\RakutenConnector\Helper\Data $dataHelper,
        \Mangoit\RakutenConnector\Helper\ManageProductRawData $manageProductRawData,
        \Mangoit\RakutenConnector\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->manageProductRawData = $manageProductRawData;
        ;
    }

    /**
     * Rakuten  product import Controller.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        $rktnClient = $this->dataHelper->getRktnClient($id);
        try {
            if ($id && $rktnClient) {
                $response = $this->manageProductRawData->getFinalProductReport();
            } else {
                $response = ['error' => 'true','error_msg' => __('Rakuten Client Does not Initialize.')];
            }
        } catch (\Exception $e) {
            $this->logger->info('backend Product Import : '.$e->getMessage());
            $response = ['error' => 'true','error_msg' => __('Something went wrong, Please check log.')];
        }
        return $resultJson->setData($response);
    }
}
