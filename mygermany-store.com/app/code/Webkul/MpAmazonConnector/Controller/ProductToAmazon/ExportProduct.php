<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\ProductToAmazon;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class ExportProduct extends \Magento\Customer\Controller\AbstractAccount
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
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Webkul\MpAmazonConnector\Helper\ProductOnAmazon $productOnAmazon
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        parent::__construct($context);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    /**
     * SyncInAmazon action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if ($this->helper->getAmzClient() && isset($params['amz_mass_export']) && is_array($params['amz_mass_export'])) {
            $result = $this->productOnAmazon->manageMageProduct($params['amz_mass_export']);
    
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
                    $this->messageManager->addWarning(
                        __("Please set product identifier code(UPC,EAN,ASIN etc) for the failed product(s).")
                    );
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
            'mpamazonconnect/producttoamazon/index'
        );
    }
}
