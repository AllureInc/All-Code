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

class Edit extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    public function execute()
    {
        $pageLabel = 'Edit/Create Block';
                $resultPage = $this->_resultPageFactory->create();
        if ($this->_mpHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpads_block_edit_layout2');
        }
                $resultPage->getConfig()->getTitle()->set(__($pageLabel));
                $blockId = $this->getRequest()->getParam("id");
        if ($blockId) {
            $block = $this->_blockDataFactory->create()->load($blockId);
            $this->_coreRegistry->register(self::CURRENT_BLOCK, $block);
        } else {
            $block = $this->_blockDataFactory->create();
            $this->_coreRegistry->register(self::CURRENT_BLOCK, $block);
        }
        return $resultPage;
    }
}
