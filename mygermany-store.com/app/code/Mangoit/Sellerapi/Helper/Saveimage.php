<?php
namespace Mangoit\Sellerapi\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Helper\Context;

class Saveimage extends \Magento\Framework\App\Helper\AbstractHelper 
{
	/**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * File interface
     *
     * @var File
     */
    protected $file;
    protected $_objectManager;

    public function __construct(
        DirectoryList $directoryList,
        File $file,
        Context $context
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->_objectManager = $this->getObjectManager();
        parent::__construct($context);
    }

    public function getObjectManager()
    {
        return $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function downloadFile($imageUrl, $tmpDir)
    {
        try {
            $image = $imageUrl;
            $imageName = basename($image);
            $ext = substr(strrchr($imageName, '.'), 1);
            $filename = rand(10,1000).time(). '.' . $ext;
            $cnt = file_get_contents($image);
            try {
                
                $fp = fopen($tmpDir .'/'.$filename, 'w+');
                fwrite($fp, $cnt);
                sleep(1);
                fclose($fp);
                chmod($tmpDir .'/'.$filename, 0777);
                return ['result'=> true, 'path'=> $tmpDir .'/'.$filename, 'filename'=> $filename];            
            } catch (Exception $e) {
                print_r($e->getMessage());
                return ['result'=> false];
            }
        } catch (Exception $e) {
            return ['result'=> false];
        }
    }

    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     */
    public function saveImageToProduct($imageUrl)
    {
        try {
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            /*$newFileName = $tmpDir .'/'. rand(10,1000).'-'.baseName($imageUrl);*/
            $is_downloaded = $this->downloadFile($imageUrl, $tmpDir);

            if ($is_downloaded['result']) {
                $newFileName = $is_downloaded['filename'];
                return $newFileName;                
            }
            
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        /*return $product;*/
    } 

    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     * ########## For base Image
     */
    public function saveBaseImageToProduct($imageUrl) 
    {
        /** @var string $tmpDir */
        $tmpDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);
        /** @var string $newFileName */

        /*return '/t/m/'. baseName($imageUrl);*/
        $baseName = baseName($imageUrl);
        $newFileName = $tmpDir .'/catalog/product/t/m/'. baseName($imageUrl);
        $is_downloaded = $this->downloadFile($imageUrl, $this->getMediaDirTmpDir().'/catalog/product/t/m');
        if ($is_downloaded['result']) {
            return '/t/m'.$is_downloaded['filename'];
        } else {
            return $newFileName;
        }
        die();
        return $newFileName;

        die("...33");

        if ($result) {
            /** add saved file to the $product gallery */
            $product->addImageToMediaGallery($newFileName, array('image', 'small_image','thumbnail'), true, true);
            $product->save();
        }
        return $result;
    } 
    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    public function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }
}