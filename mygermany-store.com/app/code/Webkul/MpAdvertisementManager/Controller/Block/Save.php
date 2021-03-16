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
namespace Webkul\MpAdvertisementManager\Controller\Block;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $blockData = $this->getRequest()->getPostValue();
        $errorStatus = $this->checkForError($blockData);
        if ($errorStatus['status'] == 1) {
            $this->messageManager->addError($errorStatus['msg']);
            if ($blockData['id'] != "") {
                return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
            }
            return $resultRedirect->setPath('mpads/block/edit');
        }
        $blockDataImage = $this->getRequest()->getFiles();

        /** check when js is disable and when only new block is created **/
        if ($blockData['id'] == '' && $blockDataImage['content']['error'] != 4 && $blockDataImage['content']['size'] != 0) {
            $errorStatus = $this->checkForError(array_reverse($blockDataImage['content']));
            if ($errorStatus['status'] == 1) {
                $this->messageManager->addError($errorStatus['msg']);
                return $resultRedirect->setPath('mpads/block/edit');
            }
        }
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
                if ($blockDataImage['content']['error']!=4) {
                    // $blockDataInterface->setImageName($blockDataImage['content']['name']);
                }
                if (isset($blockData['id'])) {
                    $blockDataInterface->setId($blockData['id']);
                }
                $blockDataInterface->setSellerId($blockData['seller_id']);
                $blockDataInterface->setAddedBy($blockData['added_by']);
                $blockDataInterface->setTitle($this->_escaper->escapeHtml($blockData['title']));
                $blockDataInterface->setUrl($blockData['url']);
                $blockId = $this->_blockRepository->save($blockDataInterface)->getId();
                if ($blockDataImage['content']['error']!=4) {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'content']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $imageAdapter = $this->_adapterFactory->create();
                    $uploader->addValidateCallback(
                        'custom_image_upload',
                        $imageAdapter,
                        'validateUploadFile'
                    );
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('webkul/MpAdvertisementManager/'.$blockData['seller_id'].'/'.$blockId);
                    $resultData = $uploader->save($path);
                    $filePath = explode('/', $resultData['file']);
                    $imageName = end($filePath);
                    $blockDataInterface->setImageName($imageName);
                    $this->_blockRepository->save($blockDataInterface);
                }
                $this->messageManager->addSuccess(__('ads block saved'));
                return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($blockData);
                $this->messageManager->addError($e->getMessage());
                $this->_coreRegistry->register(self::CURRENT_BLOCK, $blockDataInterface);
                if ($blockData['id'] != "") {
                    return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
                }
                return $resultRedirect->setPath('mpads/block/edit');
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $this->_coreRegistry->register(self::CURRENT_BLOCK, $blockDataInterface);
                if ($blockData['id'] != "") {
                    return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
                }
                $this->_blockRepository->deleteById($blockId);
                return $resultRedirect->setPath('mpads/block/edit');
            }
        }
        return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
    }

    /**
     * checkForError function check the field value during js is disable from browser
     *
     * @param array $data
     * @return array
     */
    public function checkForError($data = [])
    {
        $return = ['status' => false, 'msg' => ""];
        foreach ($data as $key => $value) {
             $status = true;
            if ($key == 'title' && $value == "") {
                $return =  ['status' => true, 'msg' => __("Title is a required field.")];
                break;
            }
            if ($key == 'url' && $value == "") {
                return ['status' => true, 'msg' => __("Url is a required field.")];
                break;
            }
            if ($key == 'url') {
                $status = preg_match(
                    "/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i",
                    $value
                );
                if (!$status) {
                    $return = ['status' => true, 'msg' => __("Please enter a valid URL. Protocol is required (http://, https:// or ftp://).")];
                    break;
                }
            }
            if ($key == 'error' && $value == 4) {
                $return = ['status' => true, 'msg' => __("Image is a required field.")];
                break;
            }
            if ($key == 'type') {
                $status = preg_match("/image\/(png|jpeg|gif)/i", $value);
                if (!$status) {
                    $return = ['status' => true, 'msg' => __("Disallowed file type.")];
                    break;
                }
            }
        }
        return $return;
    }
}
