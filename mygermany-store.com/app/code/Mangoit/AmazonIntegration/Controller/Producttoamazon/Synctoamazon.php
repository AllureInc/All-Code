<?php
/**
 * @category   Mangoit
 * @package    Mangoit_AmazonIntegration
 * @author     Mangoit Software Private Limited
 */
namespace Mangoit\AmazonIntegration\Controller\Producttoamazon;

use Webkul\AmazonMagentoConnect\Controller\Adminhtml\ProductToAmazon;
use Webkul\AmazonMagentoConnect\Helper;
use Magento\Framework\Controller\ResultFactory;
use Webkul\AmazonMagentoConnect\Model\AccountsFactory;

class Synctoamazon extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    private $helper;
    private $productOnAmazon;
   private $accountsFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Helper\Data $helper,
        Helper\ProductOnAmazon $productOnAmazon,
        AccountsFactory $accountsFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->accountsFactory = $accountsFactory;
        parent::__construct($context);
    }


    /**
     * SyncInAmazon action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        // echo "<pre>"; 
        if (isset($params['seller_id']) && ($params['seller_id'])) {
            $model = $this->accountsFactory->create()->load($params['seller_id'],'magento_seller_id');
            if (!empty($model->getData())) {
                $account_id = $model->getEntityId();
                
            }
        }

        if (isset($account_id) && $account_id) {
            $this->helper->getAmzClient($account_id);
            $result = $this->productOnAmazon->manageMageProduct($params['mageProEntityIds']);
            // print_r($account_id); 
            // print_r($result); 
            // die('died');
            if (isset($result['error']) && $result['error']) {
                $this->messageManager->addError(
                    __("Something went wrong.")
                );
            } else {
                // echo "<pre>";
                // print_r($result);
                // die('died');
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
            '*/account/exportproduct',
            [
                    'id'=> $account_id,
                    'active_tab' => 'product_sync'
                ]
        );
    }
}
