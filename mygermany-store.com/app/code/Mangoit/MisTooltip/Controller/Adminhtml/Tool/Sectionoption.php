<?php
namespace Mangoit\MisTooltip\Controller\Adminhtml\Tool;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
* 
*/
class Sectionoption extends Action
{
    protected $_objectManager;
    protected $resultPageFactory;
    protected $_store;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_store = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $sectionId = $this->getRequest()->getParam('section_id');
        $blockManager = $this->_objectManager->create('Mangoit\MisTooltip\Block\Adminhtml\Tool\Index');
        $option = $blockManager->getSectionOptions($sectionId);
        echo json_encode($option);
        // return json_encode($option);
    }
}