<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price
 */
class Name extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceFormatter;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Customer\Model\Customer $customerModel,
        array $components = [],
        array $data = []
    ) {
        $this->_priceFormatter = $priceFormatter;
        $this->_customerModel = $customerModel;
        $this->_helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customerId = $item['customer_id'];
                $customer = $this->_customerModel->load($customerId);
                $item['customerName'] = $customer->getFirstname().' '.$customer->getLastname();
                $item['email'] = $customer->getEmail();
            }
        }
        return $dataSource;
    }
}
