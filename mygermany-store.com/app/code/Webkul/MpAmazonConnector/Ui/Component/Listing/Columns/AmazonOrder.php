<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Ui\Component\Listing\Columns;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\MpAmazonConnector\Api\OrderMapRepositoryInterface;

class AmazonOrder extends Column
{
    private $orderRepository;
    private $searchCriteria;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param OrderMapRepositoryInterface $orderMapRepo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        OrderMapRepositoryInterface $orderMapRepo,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderMapRepo = $orderMapRepo;
        $this->orderRepository = $orderRepository;
        $this->searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $collection = $this->orderMapRepo->getByMagentoOrderId($item['increment_id']);
                $mapRecordModel = $this->helper->getRecordModel($collection);
                if ($mapRecordModel) {
                    $item[$this->getData('name')] = $mapRecordModel->getAmazonOrderId();
                } else {
                    $item[$this->getData('name')] = 'Not an Amazon Order';
                }
            }
        }

        return $dataSource;
    }
}
