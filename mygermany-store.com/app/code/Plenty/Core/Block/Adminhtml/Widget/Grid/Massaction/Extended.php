<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Widget\Grid\Massaction;

/**
 * Class Extended
 * @package Plenty\Core\Block\Adminhtml\Widget\Grid\Massaction
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    /**
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();

        if ($this->getMassactionIdField()) {
            $massActionIdField = $this->getMassactionIdField();
        } else {
            $massActionIdField = $this->getParentBlock()->getMassactionIdField();
        }

        // $gridIds = $allIdsCollection->getColumnValues($massActionIdField);
        $gridIds = $allIdsCollection->getAllIds();

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
