<?php 
namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Mangoit\Advertisement\Model\ResourceModel\Adsadmin\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
// Mangoit\Advertisement\Model\Adsadmin
class Edit extends Action
{
	protected $filter;
    protected $objectmanager;
    protected $resultPageFactory;
    protected $collectionFactory;
	
    protected $positionDimensions = [
            1 => '1300x200', 
            2 => '1000x500', 
            3 => '1300x200', 
            4 => '1300x200', 
            5 => '900x200', 
            6 => '900x200', 
            7 => '300x200', 
            8 => '300x200', 
            9 => '1300x200', 
            10 => '900x200', 
            11 => '500x150', 
            12 => '1300x200', 
            13 => '1300x200', 
            14 => '1300x200', 
            15 => '300x200', 
            16 => '300x200', 
            17 => '1300x200', 
            18 => '1300x200', 
            19 => '1300x200', 
            20 => '1300x200'
        ];

	public function __construct(
		Context $context,
		Filter $filter,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory
        )
	{
		$this->filter = $filter;
        $this->resultPageFactory = $resultPageFactory;
        $this->objectmanager = $objectmanager;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Edit ".$this->getCurrentBlockName()));
        $blockData = $this->getRequest()->getParams();
        return $resultPage;
	}

	public function getPostData()
    {
       $blockData = $this->getRequest()->getParams();
       return $blockData['id'];
    } 

    public function getCurrentBlockName()
    {
    	$id = $this->getRequest()->getParam('id');
    	$adminAds = $this->objectmanager->create('Mangoit\Advertisement\Model\Adsadmin');
    	$adminAds->load($id);
        // print_r($adminAds->getData());
        // die;
    	return $adminAds->getBlockName() . ' (' . $this->positionDimensions[$adminAds->getBlockPosition()] . ')';

    }
}