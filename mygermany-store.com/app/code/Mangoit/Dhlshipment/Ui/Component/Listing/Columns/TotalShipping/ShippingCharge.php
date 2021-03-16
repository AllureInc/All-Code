<?php
namespace Mangoit\Dhlshipment\Ui\Component\Listing\Columns\TotalShipping;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ShippingCharge extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
            foreach ($dataSource['data']['items'] as & $item) {
                $entity_id = $item['entity_id']; //315
                $order_id = $item['order_id']; //324
                $magerealorder_id = $item['magerealorder_id']; //000000382
                $dhlfee = $item['dhl_fees'];
                if ($dhlfee > 0) {
                    $item['total_shipping'] = $priceHelper->currency(0.00,true,false);
                } else {
                    $item['total_shipping'] = $priceHelper->currency($item['total_shipping'], true, false);
                }
            }
        }

        return $dataSource;
    }
}