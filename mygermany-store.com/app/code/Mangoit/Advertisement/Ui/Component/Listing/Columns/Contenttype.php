<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Mangoit Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Advertisement\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class BlockName.
 */
class Contenttype extends Column
{
    /**
     * @var \Mangoit\Advertisement\Helper\Data
     */
    protected $_helper;

    /**
     * __construct
     *
     * @param ContextInterface                           $context
     * @param UiComponentFactory                         $uiComponentFactory
     * @param \Mangoit\Advertisement\Helper\Data $helper
     * @param array                                      $components
     * @param array                                      $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Mangoit\Advertisement\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['content_type'] = $this->_helper->getAdvertiseContent($item['content_type']);
            }
        }
        return $dataSource;
    }
}
