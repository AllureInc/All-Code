<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order;

/**
 * Class Customerlink.
 */
class Shiptofilter extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_orderRepository;
    protected $_orderModel;

    /**
     * Constructor.
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
        Order $orderModel,
        Item $salesOrderItem,
        OrderRepository $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_orderModel = $orderModel;
        $this->_orderRepository = $orderRepository;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['entity_id'] != 0) {
                    $order  = $this->_orderRepository->get($item["entity_id"]);
                    if ($order->getShippingMethod() == 'dropship_dropship' ) {
                        $item[$fieldName] = 'Drop Shipment '.$order->getCustomerFirstname().' '.$order->getCustomerLastname();
                    } elseif ($order->getShippingMethod() == 'warehouse_warehouse') {
                        $item[$fieldName] = 'myGermany Warehouse';
                        
                    } 
                }   
            }
        }
        return $dataSource;
    }
}
