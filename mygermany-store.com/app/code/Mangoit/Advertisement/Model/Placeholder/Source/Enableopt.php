<?php

namespace Mangoit\Advertisement\Model\Placeholder\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;


class Enableopt extends Column
{

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['enable'] == 0) {
                    $item['enable'] = 'Disabled';
                } else {
                    $item['enable'] = 'Enabled';
                }
            }
        }
        return $dataSource;
    }
}
