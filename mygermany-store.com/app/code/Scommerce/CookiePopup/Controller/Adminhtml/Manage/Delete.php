<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

/**
 * Class Delete
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class Delete extends \Magento\Backend\App\Action
{
    use ManageTrait;

    const ADMIN_RESOURCE = 'Scommerce_CookiePopup::manage';

    /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface */
    private $repository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('choice_id');

        try {
            $this->repository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('The Choice has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectToEdit($id);
        }

        return $this->redirectToIndex();
    }
}
