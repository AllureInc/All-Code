<?php

namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Mangoit\Advertisement\Model\ResourceModel\Adsadmin\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;


class Changestatus extends Action
{
    protected $_resultPageFactory;
    protected $filter;
    protected $collectionFactory;
    protected $_resultPage;
    protected $_objectManager;
    protected $_session;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $_messageManager;

    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        CollectionFactory $collectionFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_messageManager = $managerInterface;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_session = $coreSession;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    public function execute()
    {
        $adminAdsModel = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        $blockData = $this->getRequest()->getParams();
        $collection = $this->filter->getCollection(
           $this->collectionFactory->create()
       );
        $status = $blockData['status'];
        if ($status == 1) {
            foreach ($collection->getData() as $item) {
                $adminAdsModel->load($item['id']);
                $adminAdsModel->setEnable(1);
                $adminAdsModel->save();
                $adminAdsModel->unsetData();

            }
        } else {
            foreach ($collection->getData() as $item) {
                $adminAdsModel->load($item['id']);
                $adminAdsModel->setEnable(0);
                $adminAdsModel->save();
                $adminAdsModel->unsetData();

            }
        }

        $this->_messageManager->addSuccess(__("Status has been changed for selected advertise."));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
        
    }
}