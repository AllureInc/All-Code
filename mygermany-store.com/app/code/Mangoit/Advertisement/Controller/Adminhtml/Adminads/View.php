<?php 

namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Mangoit\Advertisement\Model\ResourceModel\Adsadmin\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class View extends Action
{
	
	protected $_resultPageFactory;
    protected $_resultPage;
    protected $_objectManager;
    protected $resultPageFactory;
    protected $_session;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $_messageManager;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_messageManager = $managerInterface;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_session = $coreSession;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    public function execute()
    {
    	$resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("View"));
        $blockData = $this->getRequest()->getParams();
        return $resultPage;
    }
}