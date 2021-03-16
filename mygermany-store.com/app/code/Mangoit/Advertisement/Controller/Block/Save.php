<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\Advertisement\Controller\Block;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Webkul\MpAdvertisementManager\Controller\Block\Save
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $blockData = $this->getRequest()->getPostValue();
        // echo "<pre>";
        // print_r($blockData);
        // die("<br> 002");

        if (!$this->customValidations($blockData)) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
        };
        // echo "<pre>";
        // print_r(isset($blockData['product']));
        // print_r(isset($blockData['html_editor']));
        // print_r(strlen($blockData['html_editor']));
        // print_r($blockData);
        // print_r(get_class_methods($this->_blockRepository->getCollection()->getData());
        // die("<br> 002");
        
            // $contentType = $blockData['contentType'];
            // echo "<br> contentType ".$contentType;
            // die("<br>...");
            # code...
            $blockDataImage = $this->getRequest()->getFiles();
            if ($blockData) {
                $sellerData = [];
                $blockId = $this->getRequest()->getParam('id');

                if ($blockId) {
                    try {
                        $this->_blockRepository->getById($blockId);
                    } catch (\NoSuchEntityException $e) {
                        unset($blockData['id']);
                    }
                } else {
                    unset($blockData['id']);
                }
                try {
                    $blockData['seller_id'] = $this
                        ->_getSession()
                        ->getCustomer()
                        ->getId();
                    $blockData['added_by'] = "seller";
                    $blockDataInterface = $this->_blockDataFactory->create();
                    // ************************* New Work ******************************

                    if ($blockData['contentType'] != 4) {
                        if ($blockDataImage['content']['error']!=4) {
                            $blockDataInterface->setImageName($blockDataImage['content']['name']);
                        }
                    }
                    // ************************* New Work End ******************************

                    if (isset($blockData['id'])) {
                        $blockDataInterface->setId($blockData['id']);
                    }
                    $blockDataInterface->setSellerId($blockData['seller_id']);
                    $blockDataInterface->setAddedBy($blockData['added_by']);
                    $blockDataInterface->setTitle($blockData['title']);
                    $blockDataInterface->setUrl($blockData['url']);
                    // ************************* New Work ******************************
                    $blockDataInterface->setContentType($blockData['contentType']);
                    if ($blockData['contentType'] != 4) {
                        if ($blockData['contentType'] == 2) {
                            $blockDataInterface->setProductId($blockData['product']);
                            
                        }
                        
                    }
                    if ($blockData['contentType'] == 4) {
                        if (strlen($blockData['html_editor']) >= 1) {
                            $blockDataInterface->setImageName($blockData['html_editor']);                            
                        }                        
                    }
                    // ************************* New Work  End******************************

                    $blockId = $this->_blockRepository->save($blockDataInterface)->getId();
                    // ************************* New Work ******************************
                    if ($blockData['contentType'] != 4) {
                        # code...
                        if ($blockDataImage['content']['error']!=4) {
                            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'content']);
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('webkul/MpAdvertisementManager/'.$blockData['seller_id'].'/'.$blockId.'/');
                            $resultData = $uploader->save($path);
                            $filePath = explode('/',$resultData['file']);
                            $imageName = end($filePath);
                            $blockDataInterface->setImageName($imageName);
                            $this->_blockRepository->save($blockDataInterface);
                        }
                    }
                    // ************************* New Work End******************************
                    $this->messageManager->addSuccess(__('Ads block saved.'));
                    return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($blockData);
                    $this->messageManager->addError($e->getMessage());
                    $this->_coreRegistry->register(self::CURRENT_BLOCK, $blockDataInterface);
                    return $resultRedirect->setPath('mpads/block/edit');
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                    $this->_coreRegistry->register(self::CURRENT_BLOCK, $blockDataInterface);
                    return $resultRedirect->setPath('mpads/block/edit');
                }
            }
            return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
        
    }

    public function customValidations($blockData)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (isset($blockData['product'])) {
            if ($blockData['contentType'] == 2 && $blockData['product'] == 0) {
                $this->messageManager->addError(__('Please select product.'));
                return false;
            }
        }
        if (isset($blockData['title'])) {
            if ($blockData['title'] == '') {
                $this->messageManager->addError(__('Please add block title.'));
                return false;
            }
        }
        if (isset($blockData['contentType'])) {
            if ($blockData['contentType'] == 0) {
                $this->messageManager->addError(__('Please select content type.'));
                return false;
            }
        }
        if (isset($blockData['html_editor'])) {
            if ($blockData['contentType'] == 4) {
                if (strlen($blockData['html_editor']) <= 0) {
                    $this->messageManager->addError(__("Html Editor can't be empty."));
                    return false;
                    # code...
                }
            }
        }

        return true;
    }
}
