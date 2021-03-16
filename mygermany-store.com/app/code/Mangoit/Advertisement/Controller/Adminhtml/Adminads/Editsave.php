<?php

namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
// use Magento\Framework\Controller\ResultFactory; 


class Editsave extends Action
{
	protected $_resultPageFactory;
	protected $_resultPage;
    protected $_objectManager;
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
        )
	{
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
		// echo "<pre>";
		$blockData = $this->getRequest()->getParams();
        $blockDataImage = $this->getRequest()->getFiles();
        $storeHelper = $this->_objectManager->create('Magento\Store\Model\StoreRepository');
        // $this->validationForm($blockData, $blockDataImage);
        if ($this->validationForm($blockData, $blockDataImage)) 
        {
        	$webkulBlockModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        	$adminAdsModel = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        	$id = $blockData['ad_id'];
            $storeName = $this->getStoreNameOfAd($id);
            $store = $this->getStoreIdOfAd($id);
            /*echo "<br> store ".$store;
            echo "<br> storeName ".$storeName;
            die();*/
        	if (isset($blockData['product'])) {
        		$field = $blockData['product'];
        		// $store = $field['product_store'];
        		$productId = $field['product_id'];
        		$title = $field['product_title'];
        		$url = $field['product_url'];
        		$enable = $field['product_enable'];
        		/*if ($store ==  '0') {
                    $store = 'All Store';
                    $storeName = 'All Store';
                } else {
                    $storeName = $storeHelper->getActiveStoreById($store)->getName();                      
                }*/

        		$adminAdsModel->load($id);
        		$webkulAdId = $adminAdsModel->getWebkulBlockId();
        		$blockName = $adminAdsModel->getBlockName();
        		$adminAdsModel->setProductId($productId);
        		$adminAdsModel->setStoreId($store);
        		$adminAdsModel->setStoreName($storeName);
        		$adminAdsModel->setEnable($enable);
        		$adminAdsModel->setTitle($title);
        		$adminAdsModel->setUrl($url);
        		$adminAdsModel->save();

        		$webkulBlockModel->load($webkulAdId);
        		$webkulBlockModel->setProductId($productId);
        		$webkulBlockModel->setTitle($title);
        		$webkulBlockModel->setUrl($url);
        		$webkulBlockModel->save();

        		if (isset($blockDataImage['product'])) {
        			$content = 'product';
        			$field = $blockDataImage['product'];
        			$imgext = explode('/', $field['product_image']['type']);
                    $extention = end($imgext);
                    $this->imageUpload($webkulAdId, $field['product_image'], $store, $blockName, $extention, $content, $id);
                }
                $this->_messageManager->addSuccess(__("Advertisement has been saved successfully.")); 
        		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;

        	} else if (isset($blockData['category'])) {
        		$field = $blockData['category'];
        		// $store = $field['category_store'];
        		$categoryId = $field['category_id'];
        		$title = $field['category_title'];
        		$url = $field['category_url'];
        		$enable = $field['category_enable'];
        		/*if ($store ==  '0') {
                    $store = 'All Store';
                    $storeName = 'All Store';
                } else {
                    $storeName = $storeHelper->getActiveStoreById($store)->getName();                      
                }*/

        		$adminAdsModel->load($id);
        		$webkulAdId = $adminAdsModel->getWebkulBlockId();
        		$blockName = $adminAdsModel->getBlockName();
        		// $adminAdsModel->setProductId($productId);
        		$adminAdsModel->setCategoryId($categoryId);
        		$adminAdsModel->setStoreId($store);
        		$adminAdsModel->setStoreName($storeName);
        		$adminAdsModel->setEnable($enable);
        		$adminAdsModel->setTitle($title);
        		$adminAdsModel->setUrl($url);
        		$adminAdsModel->save();

        		$webkulBlockModel->load($webkulAdId);
        		$adminAdsModel->setCategoryId($categoryId);
        		// $webkulBlockModel->setProductId($productId);
        		$webkulBlockModel->setTitle($title);
        		$webkulBlockModel->setUrl($url);
        		$webkulBlockModel->save();

        		if (isset($blockDataImage['category'])) {
        			$content = 'category';
        			$field = $blockDataImage['category'];
        			$imgext = explode('/', $field['category_image']['type']);
                    $extention = end($imgext);
                    $this->imageUpload($webkulAdId, $field['category_image'], $store, $blockName, $extention, $content, $id);
                }
                $this->_messageManager->addSuccess(__("Advertisement has been saved successfully.")); 
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;

        	} else if (isset($blockData['html'])) {
        		$field = $blockData['html'];
        		// $store = $field['html_store'];
        		$htmlData = $field['html_editor'];
        		$title = $field['html_title'];
        		$url = $field['html_url'];
        		$enable = $field['html_enable'];
        		/*if ($store ==  'All Store') {
                    $storeName = 'All Store';
                    $store = 'All Store';
                } else {
                    $storeName = $storeHelper->getActiveStoreById($store)->getName();                      
                }*/

        		$adminAdsModel->load($id);
        		$webkulAdId = $adminAdsModel->getWebkulBlockId();
        		$blockName = $adminAdsModel->getBlockName();
        		// $adminAdsModel->setProductId($productId);
        		$adminAdsModel->setImageName($htmlData);
        		$adminAdsModel->setStoreId($store);
        		$adminAdsModel->setStoreName($storeName);
        		$adminAdsModel->setEnable($enable);
        		$adminAdsModel->setTitle($title);
        		$adminAdsModel->setUrl($url);
        		$adminAdsModel->save();

        		$webkulBlockModel->load($webkulAdId);
        		// $adminAdsModel->setCategoryId($categoryId);
        		$webkulBlockModel->setImageName($htmlData);
        		$webkulBlockModel->setTitle($title);
        		$webkulBlockModel->setUrl($url);
        		$webkulBlockModel->save();
                $this->_messageManager->addSuccess(__("Advertisement has been saved successfully.")); 
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;

        		
        	} else if (isset($blockData['image'])) {
        		$field = $blockData['image'];
        		// $store = $field['image_store'];
        		$title = $field['image_title'];
        		$url = $field['image_url'];
        		$enable = $field['image_enable'];
        		/*if ($store ==  'All Store') {
                    $storeName = 'All Store';
                    $store = 'All Store';
                } else {
                    $storeName = $storeHelper->getActiveStoreById($store)->getName();                      
                }*/

        		$adminAdsModel->load($id);
        		$webkulAdId = $adminAdsModel->getWebkulBlockId();
        		$blockName = $adminAdsModel->getBlockName();
        		// $adminAdsModel->setProductId($productId);
        		// $adminAdsModel->setCategoryId($categoryId);
        		$adminAdsModel->setStoreId($store);
        		$adminAdsModel->setStoreName($storeName);
        		$adminAdsModel->setEnable($enable);
        		$adminAdsModel->setTitle($title);
        		$adminAdsModel->setUrl($url);
        		$adminAdsModel->save();

        		$webkulBlockModel->load($webkulAdId);
        		// $adminAdsModel->setCategoryId($categoryId);
        		// $webkulBlockModel->setProductId($productId);
        		$webkulBlockModel->setTitle($title);
        		$webkulBlockModel->setUrl($url);
        		$webkulBlockModel->save();

        		if (isset($blockDataImage['image'])) {
        			$content = 'image';
        			$field = $blockDataImage['image'];
        			$imgext = explode('/', $field['image_image']['type']);
                    $extention = end($imgext);
                    $this->imageUpload($webkulAdId, $field['image_image'], $store, $blockName, $extention, $content, $id);
                }
                $this->_messageManager->addSuccess(__("Advertisement has been saved successfully."));    
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;

            }
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
            
        }
        // print_r($blockData);
        // print_r($blockDataImage);
        // die();
	}

	public function imageUpload($webkulAdId, $field, $store, $blockName, $extention, $content, $adminAdId)
	{
		$imgName = str_replace(" ","_", $blockName);
        $imageName = 'admin_'.$content.'_'.$imgName.'_'.rand(0,1000000).'.'.$extention;       
        $file_name = $imageName; 
        $uploaderFile = $this->_fileUploaderFactory->create(['fileId' => $field]);
        if ($store == 'All Store') {
            $store = '0';
        }

        $media = $this->_mediaDirectory->getAbsolutePath('webkul/MpAdvertisementManager/0/'.$store.'/'.$webkulAdId);
        $uploaderFile->setAllowRenameFiles(true);
        try {
                $uploaderFile->save($media, $file_name); 
                $adminAdsModelForImage = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
                $adminAdsModelForImage->load($adminAdId);
                $adminAdsModelForImage->setImageName($imageName);
                $adminAdsModelForImage->save();

                $webkulAdsModelForImage = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
                $webkulAdsModelForImage->load($webkulAdId);
                $webkulAdsModelForImage->setImageName($imageName);
                $webkulAdsModelForImage->save();
                    // print_r($adminAdsModelForImage->getData());
                    // print_r($webkulAdsModelForImage->getData());
                $adminAdsModelForImage->unsetData();
                $webkulAdsModelForImage->unsetData();
                    // echo "<br>execute";        
            } catch (Exception $e) {
                    // echo "<br>error";
            }
            $pathToFile = $media.'/'.$file_name;
            chmod($pathToFile, 0777);
	}

    public function redirectPreviousPage($id)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
       
    }

    public function getStoreNameOfAd($id)
    {
        $adminAdsModel = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        $adminAdsModel->load($id);
        return $adminAdsModel->getStoreName();
    }

    public function getStoreIdOfAd($id)
    {
        $adminAdsModel = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        $adminAdsModel->load($id);

        return $adminAdsModel->getStoreId();
    }


	public function validationForm($blockData, $blockDataImage)
	{
		$urlPatt = "/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/";
		
		if (isset($blockData['product'])) {
			$field = $blockData['product'];
			/*if ($field['product_store'] == '') {
				// echo "<br> product_store ".$field['product_store'];
				$this->_messageManager->addError(__("Please select store.")); 
				return false;
			} else*/ if ($field['product_id'] == 0) {
				// echo "<br> product_id ".$field['product_id'];
				$this->_messageManager->addError(__("Please select product.")); 
				return false;
			} else if (strlen(trim($field['product_title'])) == 0) {
				// echo "<br> product_title ".$field['product_title'];
				$this->_messageManager->addError(__("Please add title.")); 
				return false;
			} else if (strlen(trim($field['product_url'])) == 0) {
				// echo "<br> product_url ".$field['product_url'];
				$this->_messageManager->addError(__("Please add URL.")); 
				return false;
			} else if (strlen(trim($field['product_url'])) != 0) {

				if (!preg_match($urlPatt, $field['product_url'])) {
					$this->_messageManager->addError(__("Invalid Url.")); 
					return false;
				}
			} else if ($field['product_enable'] == 2) {
				// echo "<br> product_enable ".$field['product_enable'];
				$this->_messageManager->addError(__("Please select enable/disable."));
				return false; 
			}

			if (isset($blockDataImage['product'])) {
				$mainArr = $blockDataImage['product'];
				$childArr = $mainArr['product_image'];
				if ($childArr['error'] != 0) {
					$this->_messageManager->addError(__("Image is Required.")); 
				    return false;
				}

				if ($childArr['size']  > 2024000 || $childArr['size'] == 0) {
					$this->_messageManager->addError(__("Image should be lesser than 2 MB.")); 
				    return false;
				}
			}

			return true;

		} else if (isset($blockData['category'])) {
			$field = $blockData['category'];
			/*if ($field['category_store'] == '') {
				// echo "<br> category_store ".$field['category_store'];
				$this->_messageManager->addError(__("Please select store.")); 
				return false;
			} else*/ if ($field['category_id'] == 0) {
				// echo "<br> category_id ".$field['category_id'];
				$this->_messageManager->addError(__("Please select category.")); 
				return false;
			} else if (strlen(trim($field['category_title'])) == 0) {
				// echo "<br> category_title ".$field['category_title'];
				$this->_messageManager->addError(__("Please add title.")); 
				return false;
			} else if (strlen(trim($field['category_url'])) == 0) {
				// echo "<br> category_url ".$field['category_url'];
				$this->_messageManager->addError(__("Please add URL.")); 
				return false;
			} else if (strlen(trim($field['category_url'])) != 0) {

				if (!preg_match($urlPatt, $field['category_url'])) {
					$this->_messageManager->addError(__("Invalid Url.")); 
					return false;
				}
			} else if ($field['category_enable'] == 2) {
				// echo "<br> category_enable ".$field['category_enable'];
				$this->_messageManager->addError(__("Please select enable/disable."));
				return false; 
			}

			if (isset($blockDataImage['category'])) {
				$mainArr = $blockDataImage['category'];
				$childArr = $mainArr['category_image'];
				if ($childArr['error'] != 0) {
					$this->_messageManager->addError(__("Image is Required.")); 
				    return false;
				}

				if ($childArr['size']  > 2024000 || $childArr['size'] == 0) {
					$this->_messageManager->addError(__("Image should be lesser than 2 MB.")); 
				    return false;
				}
			}

			return true;

		} else if (isset($blockData['image'])) {
			$field = $blockData['image'];
			/*if ($field['image_store'] == '') {
				// echo "<br> image_store ".$field['image_store'];
				$this->_messageManager->addError(__("Please select store.")); 
				return false;
			} else */if (strlen(trim($field['image_title'])) == 0) {
				// echo "<br> image_title ".$field['image_title'];
				$this->_messageManager->addError(__("Please add title.")); 
				return false;
			} else if (strlen(trim($field['image_url'])) == 0) {
				// echo "<br> image_url ".$field['image_url'];
				$this->_messageManager->addError(__("Please add URL.")); 
				return false;
			} else if (strlen(trim($field['image_url'])) != 0) {

				if (!preg_match($urlPatt, $field['image_url'])) {
					$this->_messageManager->addError(__("Invalid Url.")); 
					return false;
				}
			} else if ($field['image_enable'] == 2) {
				// echo "<br> image_enable ".$field['image_enable'];
				$this->_messageManager->addError(__("Please select enable/disable."));
				return false; 
			}

			if (isset($blockDataImage['image'])) {
				$mainArr = $blockDataImage['image'];
				$childArr = $mainArr['image_image'];
				if ($childArr['error'] != 0) {
					$this->_messageManager->addError(__("Image is Required.")); 
				    return false;
				}

				if ($childArr['size']  > 2024000 || $childArr['size'] == 0) {
					$this->_messageManager->addError(__("Image should be lesser than 2 MB.")); 
				    return false;
				}
			}

			return true;

		} else if (isset($blockData['html'])) {
			$field = $blockData['html'];
			/*if ($field['html_store'] == '') {
				// echo "<br> html_store ".$field['html_store'];
				$this->_messageManager->addError(__("Please select store.")); 
				return false;
			} else*/ if (strlen(trim($field['html_title'])) == 0) {
				// echo "<br> html_title ".$field['html_title'];
				$this->_messageManager->addError(__("Please add title.")); 
				return false;
			} else if (strlen(trim($field['html_editor'])) == 0) {
				// echo "<br> html_title ".$field['html_title'];
				$this->_messageManager->addError(__("HTML Editor is invalid.")); 
				return false;
			} else if (strlen(trim($field['html_url'])) == 0) {
				// echo "<br> html_url ".$field['html_url'];
				$this->_messageManager->addError(__("Please add URL.")); 
				return false;
			} else if (strlen(trim($field['html_url'])) != 0) {

				if (!preg_match($urlPatt, $field['html_url'])) {
					$this->_messageManager->addError(__("Invalid Url.")); 
					return false;
				}
			} else if ($field['html_enable'] == 2) {
				// echo "<br> html_enable ".$field['html_enable'];
				$this->_messageManager->addError(__("Please select enable/disable."));
				return false; 
			}
			return true;
		}
	}
}