<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Test;

/**
 * Class Test
 * @package Scommerce\CookiePopup\Controller\Test
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Scommerce\CookiePopup\Api\ChoiceRepositoryInterface $repository */
        $repository = $this->_objectManager->create(\Scommerce\CookiePopup\Api\ChoiceRepositoryInterface::class);
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        /** @var \Scommerce\CookiePopup\Model\LinkFactory $linkFactory */
        $linkFactory = $this->_objectManager->create(\Scommerce\CookiePopup\Model\LinkFactory::class);

        //$searchCriteria = $searchCriteriaBuilder
        //->addFilter('customer_id',1,'eq')
        //->addFilter('store_id', 1, 'eq')
        //->addFilter('required', 1)
        //->addFilter('choice_id', [1, 2], 'in')
        //->create()
        //;
        //$list = $repository->getList($searchCriteria);

        /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService $service */
        $service = $this->_objectManager->create(\Scommerce\CookiePopup\Model\Service\ChoiceService::class);
        $list = $service->getCustomerChoices();
        foreach ($list->getItems() as $choice) {
            echo $choice->getChoiceName() . ' [' . ($choice->isRequired() ? 'Required' : '') .']';
            echo '<br>';
        }
        echo '----------------------------------<br>';
        echo 'Total: ' . $list->getTotalCount();
    }
}
