<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\Service;

use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;
use Scommerce\CookiePopup\Model\ResourceModel\Link;

/**
 * Class ChoiceService
 * @package Scommerce\CookiePopup\Model\Service
 */
class ChoiceService
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface */
    private $repository;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    private $criteriaBuilder;

    /** @var \Magento\Framework\Api\SortOrderBuilder */
    private $orderBuilder;

    /** @var \Scommerce\CookiePopup\Model\ChoiceFactory */
    private $choiceFactory;

    /** @var \Scommerce\CookiePopup\Model\LinkFactory */
    private $linkFactory;

    /** @var \Scommerce\CookiePopup\Helper\Data */
    private $helper;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    private $date;

    /** @var \Scommerce\CookiePopup\Model\LinkRepository */
    private $linkRepository;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $orderBuilder
     * @param \Scommerce\CookiePopup\Model\ChoiceFactory $choiceFactory
     * @param \Scommerce\CookiePopup\Model\LinkFactory $linkFactory
     * @param \Scommerce\CookiePopup\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Scommerce\CookiePopup\Model\LinkRepository $linkRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $orderBuilder,
        \Scommerce\CookiePopup\Model\ChoiceFactory $choiceFactory,
        \Scommerce\CookiePopup\Model\LinkFactory $linkFactory,
        \Scommerce\CookiePopup\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Scommerce\CookiePopup\Model\LinkRepository $linkRepository
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->repository = $repository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->orderBuilder = $orderBuilder;
        $this->choiceFactory = $choiceFactory;
        $this->linkFactory = $linkFactory;
        $this->helper = $helper;
        $this->date = $date;
        $this->linkRepository = $linkRepository;
    }

    /**
     * Get list of all choices
     *
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList()
    {
        $criteria = $this->criteriaBuilder->addSortOrder($this->getDefaultSort())->create();
        return $this->repository->getList($criteria);
    }

    /**
     * Get choices for specified customer and store
     * If customer and store not specified - return choices for current customer in current store
     *
     * @param int|null $customerId
     * @param int|null $storeId
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerChoices($customerId = null, $storeId = null)
    {
        $storeId = $this->storeManager->getStore($storeId)->getId();
        $customerId = $customerId !== null ? $customerId : $this->customerSession->getCustomerId();
        $searchCriteria = $this->criteriaBuilder
            ->addFilter(LinkInterface::CUSTOMER_ID, $customerId)
            ->addFilter(LinkInterface::STORE_ID, $storeId)
            ->addSortOrder($this->getDefaultSort())
            ->create()
        ;
        return $this->repository->getList($searchCriteria);
    }

    /**
     * Get choices for specified customer and store
     * If customer and store not specified - return choices for current customer in current store
     *
     * @param int|null $storeId
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreChoices($storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore($storeId)->getId();
        }

        $searchCriteria = $this->criteriaBuilder
            ->addFilter(LinkInterface::STORE_ID, $storeId)
            ->addSortOrder($this->getDefaultSort())
            ->create()
        ;
        return $this->repository->getList($searchCriteria);
    }

    /**
     * Helper method for get desc sort by required field
     *
     * @return \Magento\Framework\Api\SortOrder
     */
    private function getDefaultSort()
    {
        return $this->orderBuilder->setField(ChoiceInterface::REQUIRED)->setDescendingDirection()->create();
    }

    /**
     * Save choice against the customer
     *
     * @param $customerId
     * @param $choiceId
     * @param $storeId
     * @param $value
     * @throws \Exception
     */
    public function saveCustomerChoice($customerId, $choiceId, $storeId, $value)
    {
        $link = $this->linkFactory->create();
        $obj = $link->loadByCompound($customerId, $storeId, $choiceId);
        if ($obj->getId()) {
            $obj->setData(LinkInterface::STATUS, $value);
            $this->linkRepository->save($obj);
        } else {
            $link = $this->linkFactory->create();
            $data = [
                LinkInterface::CHOICE_ID    => $choiceId,
                LinkInterface::CUSTOMER_ID  => $customerId,
                LinkInterface::STORE_ID     => $storeId,
                LinkInterface::STATUS       => $value,
                LinkInterface::CREATED_AT   => $this->date->gmtDate(),
                LinkInterface::UPDATED_AT   => $this->date->gmtDate(),
            ];
            $link->setData($data);

            $this->linkRepository->save($link);
        }
    }
}
