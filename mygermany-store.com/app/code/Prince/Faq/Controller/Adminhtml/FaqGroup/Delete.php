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

class Delete extends \Prince\Faq\Controller\Adminhtml\FaqGroup
{
    /**
     * @var \Prince\Faq\Model\Faq
     */
    private $faqModel;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Prince\Faq\Model\FaqGroup
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Prince\Faq\Model\Faq $faqModel,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->faqModel = $faqModel;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('faqgroup_id');
        if ($id) {
            try {
                if($id == 1) {
                    $this->messageManager->addWarning(__('Category "Other" can not be deleted.'));
                    return $resultRedirect->setPath('*/*/');
                }
                // init model and delete
                $model = $this->_objectManager->create('Prince\Faq\Model\FaqGroup');
                $model->load($id);

                // Assign FAQs to Other category.
                $modelFaqs = $this->faqModel->getCollection();
                $modelFaqs->addFieldToFilter('group',
                    ['finset'=> [$model->getId()]]
                );
                $modelFaqs->walk([$this, 'updateFaqCategories_callback'], [$model->getId()]);

                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the FAQ Category. All the FAQs belonging to deleted category are assigned to "Other" category.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['faqgroup_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a FAQ Category to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
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
