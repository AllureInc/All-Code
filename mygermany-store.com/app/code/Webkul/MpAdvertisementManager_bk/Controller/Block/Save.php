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
                if ($blockDataImage['content']['error']!=4) {
                    $blockDataInterface->setImageName($blockDataImage['content']['name']);
                }
                if (isset($blockData['id'])) {
                    $blockDataInterface->setId($blockData['id']);
                }
                $blockDataInterface->setSellerId($blockData['seller_id']);
                $blockDataInterface->setAddedBy($blockData['added_by']);
                $blockDataInterface->setTitle($blockData['title']);
                $blockDataInterface->setUrl($blockData['url']);
                $blockId = $this->_blockRepository->save($blockDataInterface)->getId();
                if ($blockDataImage['content']['error']!=4) {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'content']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('webkul/MpAdvertisementManager/'.$blockData['seller_id'].'/'.$blockId);
                    $resultData = $uploader->save($path);
                    $filePath = explode('/',$resultData['file']);
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
                return $resultRedirect->setPath('mpads/block/edit');
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $this->_coreRegistry->register(self::CURRENT_BLOCK, $blockDataInterface);
                return $resultRedirect->setPath('mpads/block/edit');
            }
        }
        return $resultRedirect->setPath('mpads/block/edit', ['id'=>$blockId]);
    }
}
