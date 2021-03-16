<?php
namespace Mangoit\VendorAttribute\Controller\Swatch;
/**
* 
*/
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
class Swatchajax extends Action
{
	protected $_objectManager;
    protected $scopeConfig; // for email
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $resultRedirectFactory;
    protected $_inlineTranslation; // for Email
    protected $transportBuilder; //for email

	public function __construct(\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory)
	{
		$this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_objectManager = $objectmanager;
		parent::__construct($context);
	}

	public function execute()
	{
		$parameters = $this->getRequest()->getParams();
		echo "<pre>";
		print_r($parameters);
		die();
		$files = $this->getRequest()->getFiles('upload-photo');
		$media = $this->_mediaDirectory->getAbsolutePath('swatchProduct/');
		$uploaderFile = $this->_fileUploaderFactory->create(
                                ['fileId' => 'upload-photo']
                            );
		$uploaderFile->setAllowedExtensions(['jpg', 'jpeg', 'png']);
		$file_name = rand() . $_FILES['upload-photo']['name'];
		$uploaderFile->setAllowRenameFiles(true);
		if ($uploaderFile->save($media, $file_name)){
			$filePath =  $this->_mediaDirectory->getAbsolutePath('fskVerifiedUserDocs/'.$file_name);
			return $filePath;
		}
		return false;
	}
}