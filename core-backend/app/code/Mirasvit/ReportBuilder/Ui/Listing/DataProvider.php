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
 * @package   mirasvit/module-report-builder
 * @version   1.0.11
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Ui\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var ReportInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                ReportInterface::ID    => $item->getId(),
                ReportInterface::TITLE => $item->getTitle(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
