<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Advertisement
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2017 Mangoit Software Private Limited
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\Advertisement\Controller\Block;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Webkul\MpAdvertisementManager\Controller\Block\Delete
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $blockId = $this->getRequest()->getParam('blockId');

        $prepBlockIds = is_array($blockId) ? $blockId : [$blockId];
        $adsPurchaseDetail = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
        $usedBlocks = $adsPurchaseDetail->getCollection()->addFieldToFilter('block',['in' => $prepBlockIds]);

        $usedIds = array_column($usedBlocks->getData(), 'block');
        // echo "<pre>";
        // print_r($usedBlocks->getData());
        // print_r($usedIds);
        // die('dighjdfghfghdfghdfghed');
        $deletedIds = [];
        $cantDeleteIds = [];
        try {
            if ($blockId) {
                if (is_array($blockId)) {
                    foreach ($blockId as $block) {
                        if(!in_array($block, $usedIds)) {
                            $this->_blockRepository->deleteById($block);
                            $deletedIds[] = $block;
                        } else {
                            $cantDeleteIds[] = $block;
                        }
                    }
                } else {
                    if(!in_array($blockId, $usedIds)) {
                        $this->_blockRepository->deleteById($blockId);
                        $deletedIds[] = $blockId;
                    } else {
                        $cantDeleteIds[] = $blockId;
                    }
                }
            } else {
                $this->messageManager->addSuccess(__("No block provided to delete."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        if (count($deletedIds) > 0) {
             $this->messageManager->addSuccess(__("Block(s) with id %1 deleted successfully.", implode(',', $deletedIds)));
        }
        if(count($cantDeleteIds) > 0) {
            $this->messageManager->addWarning(__("Block(s) with id %1 are currently in use and can not be deleted.", implode(',', $cantDeleteIds)));
        }

        return $resultRedirect->setPath('mpads/block/index');
    }
}
