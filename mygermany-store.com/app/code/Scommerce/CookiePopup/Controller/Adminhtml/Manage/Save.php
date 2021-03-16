<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

/**
 * Class Save
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class Save extends \Magento\Backend\App\Action
{
    use ManageTrait;

    const ADMIN_RESOURCE = 'Scommerce_CookiePopup::manage';

    /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface */
    private $repository;

    /** @var \Scommerce\CookiePopup\Model\ChoiceFactory */
    private $factory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
     * @param \Scommerce\CookiePopup\Model\ChoiceFactory $factory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository,
        \Scommerce\CookiePopup\Model\ChoiceFactory $factory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (empty($data)) {
            return $this->redirectToIndex();
        }

        $id = $this->getRequest()->getParam('choice_id');

        // Handle save
        try {
            $model = $this->getModel($id);
            $model->setData($data);
            $this->_getSession()->setPageData($model->getData());
            $model = $this->repository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the Choice.'));
            $this->_getSession()->setPageData(false);
        } catch (\Exception $e) {
            return $this->handleException($e, $model, $data);
        }

        // Redirect back if requested
        return $this->getRequest()->getParam('back') ?
            $this->redirectToEdit($model->getId()) : $this->redirectToIndex();
    }

    /**
     * Helper method for get model by id or create new model
     *
     * @param string|null $id
     * @return \Magento\Framework\Model\AbstractModel|\Scommerce\CookiePopup\Api\Data\ChoiceInterface|\Scommerce\CookiePopup\Model\ResourceModel\Choice
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getModel($id = null)
    {
        return $id === null ? $this->factory->create() : $this->repository->get($id);
    }

    /**
     * Helper method for handling save exceptions
     *
     * @param \Exception $e
     * @param \Magento\Framework\Model\AbstractModel|\Scommerce\CookiePopup\Api\Data\ChoiceInterface $model
     * @param mixed $data Form POST data
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    private function handleException(\Exception $e, $model, $data)
    {
        $this->messageManager->addErrorMessage($e->getMessage());
        if ($e instanceof \Magento\Framework\Exception\NoSuchEntityException) {
            $this->_getSession()->setFormData($data);
            return $this->redirectToNew();
        }
        if ($e instanceof \Magento\Framework\Exception\CouldNotSaveException) {
            $this->_getSession()->setFormData($data);
            return $model->getId() === null ? $this->redirectToNew() : $this->redirectToEdit($model->getId());
        }
        return $this->redirectToIndex();
    }
}
