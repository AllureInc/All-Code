<?php
namespace Cnnb\AlgoliaFix\Controller\QuickView;

use Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Action\Action {

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Framework\UrlInterface
    */
    protected $urlBuilder;

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    const XML_PATH_QUICKVIEW_ENABLED = 'mgs_quickview/general/enabled';

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $requestParams = $this->getRequest()->getParams();
        $result = $this->aroundQuickViewHtml($requestParams['product']);
        $resultJson = $this->resultJsonFactory->create();
        if (strlen($result) > 1) {
            $response = ['result' => 'true', 'url'=> $result];
        } else {
            $response = ['result' => false];            
        }

        return $resultJson->setData($response);
    }

    public function aroundQuickViewHtml($productId) 
    {
        $result = '';
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $productUrl = $this->urlBuilder->getUrl('mgs_quickview/catalog_product/view', array('id' => $productId));
            return $result . '<button data-title="'. __("Quick View") .'" class="action mgs-quickview" data-quickview-url=' . $productUrl . ' title="' . __("Quick View") . '"><span class="pe-7s-search"></span></button>';
        }
        return $result;
    }

}
