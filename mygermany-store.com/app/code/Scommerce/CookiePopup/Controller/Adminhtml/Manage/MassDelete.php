<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

/**
 * Class MassDelete
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class MassDelete extends \Magento\Backend\App\Action
{
    use ManageTrait;

    const ADMIN_RESOURCE = 'Scommerce_CookiePopup::manage';

    /** @var \Magento\Ui\Component\MassAction\Filter */
    protected $filter;

    /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface */
    protected $repository;

    /** @var \Scommerce\CookiePopup\Model\ResourceModel\Choice\CollectionFactory */
    protected $collectionFactory;

    /* @var \Scommerce\CookiePopup\Helper\Data */
    private $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
     * @param \Scommerce\CookiePopup\Model\ResourceModel\Choice\CollectionFactory $collectionFactory
     * @param \Scommerce\CookiePopup\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository,
        \Scommerce\CookiePopup\Model\ResourceModel\Choice\CollectionFactory $collectionFactory,
        \Scommerce\CookiePopup\Helper\Data $helper
    ) {
        $this->filter = $filter;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (! $this->helper->isEnabled()) {
            return $this->_forward('no-route');
        }

        try {
            /** @var \Scommerce\CookiePopup\Model\ResourceModel\Choice\Collection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                /** @var \Scommerce\CookiePopup\Api\Data\ChoiceInterface $item */
                $this->repository->delete($item);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->redirectToIndex();
    }
}
