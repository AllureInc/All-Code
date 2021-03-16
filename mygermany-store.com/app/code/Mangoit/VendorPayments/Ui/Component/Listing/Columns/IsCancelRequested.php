<?php
namespace Mangoit\VendorPayments\Ui\Component\Listing\Columns;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class IsCancelRequested extends Column
{
    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->invoiceModel = $invoiceModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $cancelInvoice = $this->invoiceModel->getCollection()
                	->addFieldToFilter('canceled_order_id', $item['entity_id'])
                	->getFirstItem();

                if ($cancelInvoice->getData()) {
                    $item[$this->getData('name')] = $cancelInvoice->getCancellationPayMethod();
                } else {
                    $item[$this->getData('name')] = 'No Request';
                }
            }
        }

        return $dataSource;
    }
}
