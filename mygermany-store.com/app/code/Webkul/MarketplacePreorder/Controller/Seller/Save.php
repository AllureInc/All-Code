<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Controller\Seller ;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MarketplacePreorder\Api\PreorderSellerManagementInterface;

use Magento\Framework\App\RequestInterface;

class Save extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Error
     */
    protected $_error;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** @var PreorderSellerInterfaceFactory  */
    protected $_preorderSellerFactory;

    /**
     * @var PreorderSellerManagementInterface
     */
    protected $sellerManagement;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PreorderSellerInterfaceFactory $preorderSellerFactory,
        DataObjectHelper $dataObjectHelper,
        PreorderSellerManagementInterface $sellerManagement,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_preorderSellerFactory = $preorderSellerFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerManagement = $sellerManagement;
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
     * Default seller DHL config Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $configData = $this->getRequest()->getParams();

        $sellerDataObject = $this->_preorderSellerFactory->create();
        
        $this->dataObjectHelper->populateWithArray(
            $sellerDataObject,
            $configData,
            '\Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface'
        );
        
        if(isset($configData['type']))
            if($configData['type'])
                if (isset($configData['preorder_percent'])) {
                    $sellerDataObject->setPreorderPercent($configData['preorder_percent']);
                }
        try {
            $filterData = $this->sellerManagement->saveConfig($sellerDataObject);
        } catch (\Exception $e) {           
            $this->messageManager->addError(__($e->getMessage()));
            $this->_error = 1;
        }
        
         if(!$this->_error)
            $this->messageManager->addSuccess(
            __('Your preorder configuration has been successfully saved.')
        );
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/configuration',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
