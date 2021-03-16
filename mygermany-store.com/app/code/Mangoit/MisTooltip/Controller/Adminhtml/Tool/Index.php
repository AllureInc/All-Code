<?php
namespace Mangoit\MisTooltip\Controller\Adminhtml\Tool;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
* 
*/
class Index extends Action
{
    protected $_objectManager;
    protected $_toolModel;
    protected $_store;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\MisTooltip\Model\Tooltip $toolModel,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_toolModel = $toolModel;
        $this->_store = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Tooltip')));
        return $resultPage;
    }
}
