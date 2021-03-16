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

class Delete extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $blockId = $this->getRequest()->getParam('blockId');
        $deletedIds = [];
        try {
            if ($blockId) {
                if (is_array($blockId)) {
                    foreach ($blockId as $block) {
                        $this->_blockRepository->deleteById($block);
                        $deletedIds[] = $block;
                    }
                } else {
                    $this->_blockRepository->deleteById($blockId);
                    $deletedIds[] = $blockId;
                }
            } else {
                $this->messageManager->addSuccess(__("no block provided to delete"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        if (count($deletedIds) > 0) {
             $this->messageManager->addSuccess(__("block(s) with id %1 deleted successfully", implode(',', $deletedIds)));
        }

        return $resultRedirect->setPath('mpads/block/index');
    }
}
