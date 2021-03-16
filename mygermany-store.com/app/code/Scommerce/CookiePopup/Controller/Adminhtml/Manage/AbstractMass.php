<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractMass
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
abstract class AbstractMass extends \Magento\Backend\App\Action
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
            $key = (int) $this->getRequest()->getParam($this->getRequestKey(), -1);
            if (! in_array($key, $this->getValues())) {
                throw new LocalizedException($this->getWrongMessage($key));
            }
            /** @var \Scommerce\CookiePopup\Model\ResourceModel\Choice\Collection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                /** @var \Scommerce\CookiePopup\Api\Data\ChoiceInterface $item */
                $this->repository->save($this->modify($item, $key));
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been changed.', $collectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->redirectToIndex();
    }

    /**
     * Get request key
     *
     * @return string
     */
    abstract protected function getRequestKey();

    /**
     * Get available values
     *
     * @return array
     */
    abstract protected function getValues();

    /**
     * Get wrong message
     *
     * @param int $key
     * @return \Magento\Framework\Phrase
     */
    abstract protected function getWrongMessage($key);

    /**
     * Modify item
     *
     * @param \Scommerce\CookiePopup\Api\Data\ChoiceInterface $item
     * @param int $key
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceInterface
     */
    abstract protected function modify($item, $key);
}
