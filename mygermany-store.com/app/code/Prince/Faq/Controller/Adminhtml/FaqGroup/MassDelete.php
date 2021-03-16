<?php

/**
 * MagePrince
 * Copyright (C) 2018 Mageprince
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html
 *
 * @category MagePrince
 * @package Prince_Faq
 * @copyright Copyright (c) 2018 MagePrince
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MagePrince
 */

namespace Prince\Faq\Controller\Adminhtml\FaqGroup;

class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Prince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Prince\Faq\Model\Faq
     */
    private $faqModel;
    
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Prince\Faq\Model\ResourceModel\FaqGroup\CollectionFactory $collectionFactory,
        \Prince\Faq\Model\Faq $faqModel,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->faqModel = $faqModel;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $logCollection = $this->filter->getCollection($this->collectionFactory->create());
            $itemsDeleted = 0;
            foreach ($logCollection as $item) {

                if($item->getId() == 1) {
                    $this->messageManager->addWarning(__('Category "Other" can not be deleted.'));
                    continue;
                }

                // Assign FAQs to Other category.
                $modelFaqs = $this->faqModel->getCollection();
                $modelFaqs->addFieldToFilter('group',
                    ['finset'=> [$item->getId()]]
                );
                $modelFaqs->walk([$this, 'updateFaqCategories_callback'], [$item->getId()]);

                $item->delete();
                $itemsDeleted++;
            }
            if($itemsDeleted > 0) {
                $this->messageManager->addSuccess(__('A total of %1 FAQ Category(s) were deleted. All the FAQs belonging to deleted category(s) are assigned to "Other" category.', $itemsDeleted));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('prince_faq/faqgroup');
    }

    public function updateFaqCategories_callback($item, $faqCatId){

        $groups = explode(',', $item->getGroup());
        if (($key = array_search($faqCatId, $groups)) !== false) {
            unset($groups[$key]);
        }
        // Adding Others group id to the FAQ.
        $groups[] = 1;
        $updatedGroups = implode(',', $groups);
        $item->setGroup($updatedGroups);
        $item->save();
    }
}
