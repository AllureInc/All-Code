<?php

namespace Mangoit\Advertisement\Controller\Adminhtml;

/**
* 
*/
class Blockimage extends \Magento\Backend\App\Action
{
	protected $_objectManager;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $resultRedirectFactory;
    protected $reader;
    protected $ioAdapter;

	public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\Filesystem\Io\File $ioAdapter
    ){
    	$this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_objectManager = $objectmanager;
        $this->resultRedirectFactory = $resultRedirect;
        $this->reader = $reader;
        $this->ioAdapter = $ioAdapter;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $parameters = $this->getRequest()->getParams();
        $files = $this->getRequest()->getFiles();
        echo "<pre>";
        print_r($files);
        die();
        $media = $this->_mediaDirectory->getAbsolutePath('fskVerifiedUserDocs/');
        $uploaderFile = $this->_fileUploaderFactory->create(
                                ['fileId' => 'uploadFile']
                            );
        $uploaderFile->setAllowedExtensions(['jpg', 'jpeg', 'png', 'pdf']);
        $file_name = rand().$_FILES['uploadFile']['name'];
        $uploaderFile->setAllowRenameFiles(true);
        $file_type = $_FILES['uploadFile']['type'];
        $uploaderFile->save($media, $file_name);
        $resultRedirect->setRefererUrl();
		return $resultRedirect;
    }
}