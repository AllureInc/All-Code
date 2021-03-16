<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Plenty\Core\Model\Source\Status as StatusFactory;

/**
 * Class Status
 */
class Status extends Column
{
    /**
     * @var string[]
     */
    protected $_statuses;

    /**
     * Status constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StatusFactory $statusFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StatusFactory $statusFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_statuses = $statusFactory->toOptionHashImportExportStatus(); // $collectionFactory->create()->toOptionHash();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $item[$this->getData('name')] = isset($this->_statuses[$item[$this->getData('name')]])
                    ? $this->_statuses[$item[$this->getData('name')]]
                    : $item[$this->getData('name')];
            }
        }

        return $dataSource;
    }
}
