<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\DataSource;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;
use Mirasvit\Report\Api\Service\IntervalInterface;
use \Mirasvit\Dashboard\Ui\Block\Form\DataProvider as BlockDataProvider;

class Context
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var DateServiceInterface
     */
    public $dateService;

    public function __construct(
        RequestInterface $request,
        DateServiceInterface $dateService
    ) {
        $this->request = $request;
        $this->dateService = $dateService;
    }

    public function getParam($key)
    {
        return $this->request->getParam($key);
    }

    /**
     * @param BlockInterface $block
     * @return IntervalInterface
     */
    public function getDateRange(BlockInterface $block)
    {
        $interval = $this->dateService->getInterval(DateServiceInterface::LIFETIME);

        $filter = $this->getParam('filters');

        if (isset($filter['date_range'])) {
            $interval->setFrom(new \Zend_Date(strtotime($filter['date_range']['from'] . ' 00:00:00')));
            $interval->setTo(new \Zend_Date(strtotime($filter['date_range']['to'] . ' 23:59:59')));
        }

        if ($block->getData('time/override')) {
            $interval = $this->dateService->getInterval($block->getData('time/range'));
        }

        return $interval;
    }

    /**
     * @param BlockInterface|\Magento\Framework\DataObject $block
     * @return IntervalInterface|false
     */
    public function getComparisonDateRange(BlockInterface $block)
    {
        if ($block->getData('time/override')
            && isset($block['time']['compare'])
            && $block->getData('time/compare') !== BlockDataProvider::TIME_COMPARISON_DISABLED
        ) {
            return $this->dateService->getPreviousInterval(
                $block->getData('time/range'),
                $block->getData('time/compare')
            );
        }

        $filter = $this->getParam('filters');
        if (isset($filter['date_range']['comparisonEnabled'])
            && $filter['date_range']['comparisonEnabled']
            && $filter['date_range']['compareFrom']
            && $filter['date_range']['compareTo']) {
            $interval = $this->dateService->getInterval(DateServiceInterface::LIFETIME);
            $interval->setFrom(new \Zend_Date(strtotime($filter['date_range']['compareFrom'] . ' 00:00:00')));
            $interval->setTo(new \Zend_Date(strtotime($filter['date_range']['compareTo'] . ' 23:59:59')));

            return $interval;
        }

        return false;
    }
}