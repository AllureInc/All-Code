<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\ExportProduct;

use Mangoit\RakutenConnector\Controller\Adminhtml\ExportProduct;
use Mangoit\RakutenConnector\Helper;
use Magento\Framework\Controller\ResultFactory;

class SyncToAmazon extends ExportProduct
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Helper\Data $helper,
        Helper\ProductOnRakuten $productOnRakuten
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->productOnRakuten = $productOnRakuten;
    }

    /**
     * SyncInAmazon action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id']) && $params['id']) {
            $this->helper->getRktnClient($params['id']);
            $result = $this->productOnRakuten->manageMageProduct($params['mageProEntityIds']);
            if (isset($result['error']) && $result['error']) {
                $this->messageManager->addError(
                    __("Something went wrong.")
                );
            } else {
                if (!empty($result['count'])) {
                    $this->messageManager->addSuccess(
                        __("A total of %1 record(s) have been exported to amazon.", $result['count'])
                    );
                }
    
                if (isset($result['error_count']) && !empty($result['error_count'])) {
                    $this->messageManager->addError(
                        __("A total of %1 record(s) have been failed to export at amazon.", $result['error_count'])
                    );
                    // $this->messageManager->addWarning(
                    //     __("Please set product identifier code(UPC,EAN,ASIN etc) for the failed product(s).")
                    // );
                }
            }
        } else {
            $this->messageManager->addError(
                __("Invalid parameters.")
            );
        }
        
        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                    'id'=>$params['id'],
                    'active_tab' => 'product_sync'
                ]
        );
    }
}
