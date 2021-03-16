<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

/**
 * Class Edit
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class Edit extends \Magento\Backend\App\Action
{
    use ManageTrait;

    const ADMIN_RESOURCE = 'Scommerce_CookiePopup::manage';

    /** @var \Magento\Framework\View\Result\PageFactory */
    private $resultPageFactory;

    /* @var \Scommerce\CookiePopup\Model\ChoiceRegistry */
    private $registry;

    /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface */
    private $repository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     * @param \Scommerce\CookiePopup\Model\ChoiceRegistry $registry
     * @param \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Scommerce\CookiePopup\Model\ChoiceRegistry $registry,
        \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('choice_id');
        return $id === null ? $this->newChoice() : $this->editChoice($id);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    private function newChoice()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('New Choice'));
        return $resultPage;
    }

    /**
     * @param int|string $id
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    private function editChoice($id)
    {
        try {
            $model = $this->repository->get($id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectToIndex();
        }
        $formData = $this->_getSession()->getFormData(true);
        if (! empty($formData)) {
            $model->setData($formData);
        }
        $model->setId($id); // As this field overrided by setData
        $this->registry->set($model);
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Choice: %1 (%2)', $model->getChoiceName(), $id));
        return $resultPage;
    }

    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\App\ResponseInterface
     */
    private function createPage()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Manage Choice List'), __('Manage Choice List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Popup'));
        return $resultPage;
    }
}
