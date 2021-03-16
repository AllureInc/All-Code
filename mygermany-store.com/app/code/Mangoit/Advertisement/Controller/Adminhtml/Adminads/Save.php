<?php

namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $_resultPage;

    protected $_objectManager;
    protected $_session;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $_messageManager;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
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
        
        $allStores = [];
        $adminAdsModel = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
        $storeHelper = $this->_objectManager->create('Magento\Store\Model\StoreRepository');
        $webkulAdsModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        // $webkulAdsPricingModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
        $webkulAdsPricingModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        // print_r(get_class_methods($storeHelper));
        foreach ($storeHelper->getList() as $storeItem) {
            array_push($allStores, $storeItem['store_id']);
        }
        $blockData = $this->getRequest()->getParams();
        $blockDataImage = $this->getRequest()->getFiles();
        if (! $this->checkValidations($blockData, $blockDataImage)) {
            return $this->resultRedirectFactory->create()->setPath('*/adminads/newads/',
                ['_secure' => $this->getRequest()->isSecure()] );
         }
        //  echo "<pre>";
        // print_r($blockData);
        // var_dump(isset($blockData['image']));
        // var_dump(isset($blockData['product']));
        // var_dump(isset($blockData['category']));
        // var_dump(isset($blockData['html']));
        //  die();
        $adBlockId = $blockData['internalBlock'];
        $adBlockName = $blockData['block_name'];
        $adBlockPosition = $blockData['internalBlock'];
        $webkulAdsPricingModel->load($adBlockPosition, 'block_position');
        $blockAdsPrice = $webkulAdsPricingModel->getPrice();
        $adBlockContentType = $blockData['content_type'];
        $adValidity = $this->getBlockValidity($adBlockId);

        
        $contentType = $blockData['content_type'];
        if ($contentType != 4) {
            if ($contentType == 1) {
                if (!isset($blockData['image'])) {
                        $this->_messageManager->addError(__("Advertise block can not be save empty. Please add at least one ad."));    
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                $blockIdArray = [];
                $adminAdIdArray = [];
                $storeIdArray = [];
                $imageCounter = 0;

                foreach ($blockData['image'] as $key => $value) {
                    array_push($storeIdArray, $value['imageStore']);
                }

                try {
                    
                    $webkulBlockModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
                    $webkulPurchaseModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
                    foreach ($blockData['image'] as $item) {
                        if ($item['imageStore'] ==  'All Store') {
                            $storeName = 'All Store';
                        } else {
                            $storeName = $storeHelper->getActiveStoreById($item['imageStore'])->getName();                      
                        }
                        $webkulBlockModel->setSellerId('0');
                        $webkulBlockModel->setAdType('1');
                        $webkulBlockModel->setContentType($adBlockContentType);
                        $webkulBlockModel->setTitle($item['imageTitle']);
                        $webkulBlockModel->setUrl($item['imageUrl']);
                        $webkulBlockModel->setAddedBy('admin');
                        $webkulBlockModel->save(); /*uncomment *******************/
                        $blockSavedId =  $webkulBlockModel->getId();
                        array_push($blockIdArray, $blockSavedId);

                        $adminAdsModel->setSellerId('0');
                        $adminAdsModel->setAdType('1');
                        $adminAdsModel->setContentType($adBlockContentType);
                        $adminAdsModel->setBlockPosition($adBlockPosition);
                        $adminAdsModel->setBlockName($adBlockName);
                        $adminAdsModel->setValidFor($adValidity);
                        $adminAdsModel->setStoreId($item['imageStore']);
                        $adminAdsModel->setStoreName($storeName);
                        $adminAdsModel->setEnable(1);
                        $adminAdsModel->setTitle($item['imageTitle']);
                        $adminAdsModel->setUrl($item['imageUrl']);
                        $adminAdsModel->setAddedBy('admin');
                        $adminAdsModel->setWebkulBlockId($blockSavedId);
                        $adminAdsModel->save(); /* uncomment *******************/
                        $adminBlockSavedId =  $adminAdsModel->getId();
                        array_push($adminAdIdArray, $adminBlockSavedId);
                        $adminAdsModel->unsetData();
                        $webkulBlockModel->unsetData();
                        
                        
                    } 
                    
                    foreach ($blockDataImage['imageArr'] as $imageItem) {
                        $content = 'image';
                        $imgName = str_replace(" ","_",$blockData['block_name']);
                        $imgext = explode('/',$imageItem['type']);
                        $imageName = 'admin_'.$imgName.'_'.rand(0,1000000).'.'.end($imgext);
                        $extention = end($imgext);
                        $fileId = $imageItem;
                        $storeId = $storeIdArray[$imageCounter];
                        $blockId = $blockIdArray[$imageCounter];
                        $adminBlockId = $adminAdIdArray[$imageCounter];
                        $this->uploadImages($blockData, $extention, $content, $fileId, $storeId, $blockId, $allStores, $adminBlockId);
                        $imageCounter++;
                    }
                    $this->_messageManager->addSuccess(__("Internal advertisement block has been saved successfully."));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );      
                } catch (Exception $e) {
                    $this->_messageManager->addSuccess(__("Something went wrong"));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } elseif ($contentType == 2) {
                if (!isset($blockData['product'])) {
                        $this->_messageManager->addError(__("Advertise block can not be save empty. Please add at least one ad."));    
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                }
                $blockIdArray = [];
                $adminAdIdArray = [];
                $productCounter = 0;
                $storeIdArray = [];

                foreach ($blockData['product'] as $key => $value) {
                    array_push($storeIdArray, $value['product_store']);
                }

                try {
                    $webkulBlockModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
                    $webkulPurchaseModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
                    foreach ($blockData['product'] as $item) {
                        if ($item['product_store'] ==  'All Store') {
                            $storeName = 'All Store';
                        } else {
                            $storeName = $storeHelper->getActiveStoreById($item['product_store'])->getName();                   
                        }
                        $webkulBlockModel->setSellerId('0');
                        $webkulBlockModel->setAdType('1');
                        $webkulBlockModel->setContentType($adBlockContentType);
                        $webkulBlockModel->setProductId($item['product_id']);
                        $webkulBlockModel->setTitle($item['product_title']);
                        $webkulBlockModel->setUrl($item['product_url']);
                        $webkulBlockModel->setAddedBy('admin');
                        $webkulBlockModel->save(); /*uncomment *******************/
                        $blockSavedId =  $webkulBlockModel->getId();
                        array_push($blockIdArray, $blockSavedId);

                        $adminAdsModel->setSellerId('0');
                        $adminAdsModel->setAdType('1');
                        $adminAdsModel->setProductId($item['product_id']);
                        $adminAdsModel->setContentType($adBlockContentType);
                        $adminAdsModel->setBlockPosition($adBlockPosition);
                        $adminAdsModel->setBlockName($adBlockName);
                        $adminAdsModel->setValidFor($adValidity);
                        $adminAdsModel->setStoreId($item['product_store']);
                        $adminAdsModel->setStoreName($storeName);
                        $adminAdsModel->setEnable(1);
                         $adminAdsModel->setTitle($item['product_title']);
                        $adminAdsModel->setUrl($item['product_url']);
                        $adminAdsModel->setAddedBy('admin');
                        $adminAdsModel->setWebkulBlockId($blockSavedId);
                        $adminAdsModel->save(); /* uncomment *******************/
                        $adminBlockSavedId =  $adminAdsModel->getId();
                        array_push($adminAdIdArray, $adminBlockSavedId);
                        $adminAdsModel->unsetData();
                        $webkulBlockModel->unsetData();
                        
                        
                    } 
                    
                    foreach ($blockDataImage['productImgFile'] as $productItem) {
                            $content = 'product';
                            $imgext = explode('/', $productItem['type']);
                            $extention = end($imgext);
                            $fileId = $productItem;
                                $storeId = $storeIdArray[$productCounter];
                                $blockId = $blockIdArray[$productCounter];
                                $adminBlockId = $adminAdIdArray[$productCounter];
                                $this->uploadImages($blockData, $extention, $content, $fileId, $storeId, $blockId, $allStores, $adminBlockId);                        
                            $productCounter++;
                    
                    }
                    $this->_messageManager->addSuccess(__("Internal advertisement block has been saved successfully."));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );    
                } catch (Exception $e) {
                    $this->_messageManager->addSuccess(__("Something went wrong"));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                
            } elseif ($contentType == 3) {

                if (!isset($blockData['category'])) {
                        $this->_messageManager->addError(__("Advertise block can not be save empty. Please add at least one ad."));    
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }

                $blockIdArray = [];
                $adminAdIdArray = [];

                $categoryCounter = 0;
                $storeIdArray = [];
                foreach ($blockData['category'] as $key => $value) {
                    array_push($storeIdArray, $value['category_store']);
                }

                try {
                    $webkulBlockModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
                    $webkulPurchaseModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
                    foreach ($blockData['category'] as $item) {
                        if ($item['category_store'] ==  'All Store') {
                            $storeName = 'All Store';
                        } else {
                            $storeName = $storeHelper->getActiveStoreById($item['category_store'])->getName();                  
                        }
                        // $storeName = $storeHelper->getActiveStoreById($item['category_store'])->getName();
                        $webkulBlockModel->setSellerId('0');
                        $webkulBlockModel->setAdType('1');
                        $webkulBlockModel->setContentType($adBlockContentType);
                        $webkulBlockModel->setCategoryId($item['category_id']);
                        $webkulBlockModel->setTitle($item['category_title']);
                        $webkulBlockModel->setUrl($item['category_url']);
                        $webkulBlockModel->setAddedBy('admin');
                        $webkulBlockModel->save(); /*uncomment *******************/
                        $blockSavedId =  $webkulBlockModel->getId();
                        array_push($blockIdArray, $blockSavedId);

                        $adminAdsModel->setSellerId('0');
                        $adminAdsModel->setAdType('1');
                        // $adminAdsModel->setSellerId('0');
                        // $adminAdsModel->setProductId('165');
                        $adminAdsModel->setCategoryId($item['category_id']);
                        $adminAdsModel->setContentType($adBlockContentType);
                        // $adminAdsModel->setItemId('000');
                        $adminAdsModel->setBlockPosition($adBlockPosition);
                        // $adminAdsModel->setBlock($blockSavedId);
                        // $adminAdsModel->setPrice($blockAdsPrice);
                        $adminAdsModel->setBlockName($adBlockName);
                        $adminAdsModel->setValidFor($adValidity);
                        // $adminAdsModel->setEnable(1);
                        $adminAdsModel->setStoreId($item['category_store']);
                        $adminAdsModel->setStoreName($storeName);
                        $adminAdsModel->setEnable(1);
                         $adminAdsModel->setTitle($item['category_title']);
                        $adminAdsModel->setUrl($item['category_url']);
                        $adminAdsModel->setAddedBy('admin');
                        $adminAdsModel->setWebkulBlockId($blockSavedId);
                        $adminAdsModel->save(); /* uncomment *******************/
                        $adminBlockSavedId =  $adminAdsModel->getId();
                        array_push($adminAdIdArray, $adminBlockSavedId);
                        $adminAdsModel->unsetData();
                        $webkulBlockModel->unsetData();
                        
                    } 
                        foreach ($blockDataImage['categoryImgFile'] as $categoryItem) {
                            $content = 'category';
                            $imgext = explode('/', $categoryItem['type']);
                            $extention = end($imgext);
                            $fileId = $categoryItem;
                                $storeId = $storeIdArray[$categoryCounter];
                                $blockId = $blockIdArray[$categoryCounter];
                                $adminBlockId = $adminAdIdArray[$categoryCounter];
                                $this->uploadImages($blockData, $extention, $content, $fileId, $storeId, $blockId, $allStores, $adminBlockId);                        
                            $categoryCounter++;
                        }
                    $this->_messageManager->addSuccess(__("Internal advertisement block has been saved successfully."));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );      
                } catch (Exception $e) {
                    $this->_messageManager->addSuccess(__("Something went wrong"));    
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

            }
        } else {
            if (!isset($blockData['html'])) {
                        $this->_messageManager->addError(__("Advertise block can not be save empty. Please add at least one ad."));    
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    

            $htmlStore = $blockData['html']['html_store'];
            $htmlData = $blockData['html']['html_editor'];
            $htmlUrl = $blockData['html']['html_url'];
            $htmlTitle = $blockData['html']['html_title'];
            $webkulBlockModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
            $validation = $this->htmlValidation($htmlStore, $htmlData, $htmlUrl, $htmlTitle);
            if ($validation) {
                        if ($htmlStore ==  'All Store') {
                            $storeName = 'All Store';
                        } else {
                            $storeName = $storeHelper->getActiveStoreById($htmlStore)->getName();                      
                        }
                        $webkulBlockModel->setSellerId('0');
                        $webkulBlockModel->setAdType('1');
                        $webkulBlockModel->setContentType($adBlockContentType);
                        $webkulBlockModel->setTitle($htmlTitle);
                        $webkulBlockModel->setUrl($htmlUrl);
                        $webkulBlockModel->setImageName($htmlData);
                        $webkulBlockModel->setAddedBy('admin');
                        $webkulBlockModel->save(); /*uncomment *******************/
                        $blockSavedId =  $webkulBlockModel->getId();
                        // array_push($blockIdArray, $blockSavedId);

                        $adminAdsModel->setSellerId('0');
                        $adminAdsModel->setAdType('1');
                        $adminAdsModel->setContentType($adBlockContentType);
                        $adminAdsModel->setBlockPosition($adBlockPosition);
                        $adminAdsModel->setBlockName($adBlockName);
                        $adminAdsModel->setValidFor($adValidity);
                        $adminAdsModel->setStoreId($htmlStore);
                        $adminAdsModel->setStoreName($storeName);
                        $adminAdsModel->setEnable(1);
                        $adminAdsModel->setTitle($htmlTitle);
                        $adminAdsModel->setUrl($htmlUrl);
                        $adminAdsModel->setImageName($htmlData);
                        $adminAdsModel->setAddedBy('admin');
                        $adminAdsModel->setWebkulBlockId($blockSavedId);
                        $adminAdsModel->save(); /* uncomment *******************/
                        // $adminBlockSavedId =  $adminAdsModel->getId();
                        // array_push($adminAdIdArray, $adminBlockSavedId);
                        $adminAdsModel->unsetData();
                        $webkulBlockModel->unsetData();
            } else {    
                return $this->resultRedirectFactory->create()->setPath(
                    '*/adminads/newads/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );   
            }
            $this->_messageManager->addSuccess(__("Internal advertisement block has been saved successfully."));    
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function uploadImages($blockData, $extention, $content, $fileId, $storeId, $blockId, $allStores, $adminBlockId)
    {
        $uploaderFile = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
        if ($storeId == 'All Store') {
            $storeId = '0';
        }

        // $imgName = preg_replace('/\W(?![^.]*$)/', '_', $blockData['block_name']);
        $imgName = $uploaderFile->getCorrectFileName($blockData['block_name']);
        $imageName = 'admin_'.$content.'_'.$imgName.'_'.rand(0,1000000).'.'.$extention;       
        $file_name = $imageName;   

        $media = $this->_mediaDirectory->getAbsolutePath('webkul/MpAdvertisementManager/0/'.$storeId.'/'.$blockId.'/');
        $uploaderFile->setAllowRenameFiles(true);
        try {
            $uploaderFile->save($media, $file_name); 
            $adminAdsModelForImage = $this->_objectManager->create('Mangoit\Advertisement\Model\Adsadmin');
            $adminAdsModelForImage->load($adminBlockId);
            $adminAdsModelForImage->setImageName($imageName);
            $adminAdsModelForImage->save();

            $webkulAdsModelForImage = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Block');
            $webkulAdsModelForImage->load($blockId);
            $webkulAdsModelForImage->setImageName($imageName);
            $webkulAdsModelForImage->save();

            $adminAdsModelForImage->unsetData();
            $webkulAdsModelForImage->unsetData();
        } catch (Exception $e) {}
        $pathToFile = $media.'/'.$file_name;
        chmod($pathToFile, 0777);
    }

    public function getBlockValidity($blockId)
    {
        $webkulHelper =  $this->_objectManager->create('Webkul\MpAdvertisementManager\Helper\Data');
        $blockData = $webkulHelper->getSettingsById($blockId);
        if ($blockData['valid_for']) {
            return $blockData['valid_for'];
        }
    }

    public function goBack()
    {
        return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
    }

    public function htmlValidation($htmlStore, $htmlData, $htmlUrl, $htmlTitle)
    {
        $urlPatt = "/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/";
        /*if ($htmlStore == 0) {
             $this->_messageManager->addError(__("Please select store.")); 
            return false;
        }*/

        if (strlen(trim($htmlUrl)) == 0) {
             $this->_messageManager->addError(__("Url is required")); 
            return false;
        } else {
            if (!preg_match($urlPatt, $htmlUrl))
            {
                 $this->_messageManager->addError(__("Invalid Url.")); 
                return false;
            }
        }

        if (strlen(trim($htmlTitle)) == 0) {
             $this->_messageManager->addError(__("Title can not be empty.")); 
            return false;
        }

        if (strlen(trim($htmlData)) == 0) {
             $this->_messageManager->addError(__("HTML Editor can not be empty.")); 
            return false;
        }

        return true;
    }

    public function checkValidations($blockData, $blockDataImage)
    {
        // echo "<pre>";
        /*print_r($blockData);
        print_r($blockDataImage);*/
        if ($blockData['internalBlock'] == 0) {
            $this->_messageManager->addError(__("Please select an advertisement block."));    
            return false;
        }

        if (isset($blockData['product'])) {
            $urlPatt = "/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/";

           foreach ($blockData['product'] as $item) {
               if ($item['product_id'] == '0') {
                   $message = 'Please select product.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if (strlen(trim($item['product_url'])) <= 1) {
                   $message = 'Product url is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               } else if (! preg_match($urlPatt,$item['product_url'])) {
                   $message = 'Product url is not valid.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if (strlen(trim($item['product_title'])) <= 0) {
                   $message = 'Product title is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }

           foreach ($blockDataImage['productImgFile'] as $item) {
               if ($item['error'] == 4) {
                   $message = 'Image required.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if ($item['size'] > 2024000 || $item['size'] == 0) {
                   $message = 'Image should be lesser than 2 MB.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }
        }


        if (isset($blockData['category'])) {
            $urlPatt = "/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/";
           // print_r($blockData['category']);

           foreach ($blockData['category'] as $item) {
               if ($item['category_id'] == '0') {
                   $message = 'Please select category.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if (strlen(trim($item['category_url'])) <= 1) {
                   $message = 'category url is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               } else if (! preg_match($urlPatt,$item['category_url'])) {
                   $message = 'category url is not valid.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if (strlen(trim($item['category_title'])) <= 0) {
                   $message = 'category title is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }

           foreach ($blockDataImage['categoryImgFile'] as $item) {
               if ($item['error'] == 4) {
                   $message = 'Image required.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if ($item['size'] > 2024000 || $item['size'] == 0) {
                   $message = 'Image should be lesser than 2 MB.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }

        }

        if (isset($blockData['image'])) {
            $urlPatt = "/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/";

           foreach ($blockData['image'] as $item) {

               if (strlen(trim($item['imageUrl'])) <= 1) {
                   $message = 'image url is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               } else if (! preg_match($urlPatt,$item['imageUrl'])) {
                   $message = 'image url is not valid.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if (strlen(trim($item['imageTitle'])) <= 0) {
                   $message = 'image title is empty.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }

           print_r($blockDataImage['imageArr']);

           foreach ($blockDataImage['imageArr'] as $item) {
               if ($item['error'] == 4) {
                   $message = 'Image required.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

               if ($item['size'] > 2024000 || $item['size'] == 0) {
                   $message = 'Image should be lesser than 2 MB.';
                   $this->_messageManager->addError(__($message)); 
                   return false;
               }

           }

        }
        
        return true;
    }

    public function redirectPrevious($message)
    {
        $this->_messageManager->addError(__($message)); 
        return $this->resultRedirectFactory->create()->setPath('*/*/',
                ['_secure' => $this->getRequest()->isSecure()] );
    }

}
