<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\ProductToRakuten;

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
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Mangoit\RakutenConnector\Helper\ProductOnRakuten $productOnRakuten
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->helper = $helper;
        $this->productOnRakuten = $productOnRakuten;
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

        if ($this->helper->getRktnClient() && isset($params['rktn_mass_export']) && is_array($params['rktn_mass_export'])) {
            $result = $this->productOnRakuten->manageMageProduct($params['rktn_mass_export']);
    
            if (isset($result['error']) && $result['error']) {
                $this->messageManager->addError(
                    __("Something went wrong.")
                );
            } else {
                if (!empty($result['count'])) {
                    $this->messageManager->addSuccess(
                        __("A total of %1 record(s) have been exported to rakuten.", $result['count'])
                    );
                }
    
                if (isset($result['error_count']) && !empty($result['error_count'])) {
                    $this->messageManager->addError(
                        __("A total of %1 record(s) have been failed to export at rakuten.", $result['error_count'])
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
            'rakutenconnect/producttorakuten/index'
        );
    }
}
