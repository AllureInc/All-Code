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
use Webkul\Walletsystem\Helper\Data;
 
class CreditRuleAmount extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceFormatter;

    protected $_walletHelper;

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
        Data $walletHelper,
        array $components = [],
        array $data = []
    ) {
        $this->_priceFormatter = $priceFormatter;
        $this->_walletHelper = $walletHelper;
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
                if ($this->_walletHelper->getPriceType()) {
                    $item[$this->getData('name')] = $item[$this->getData('name')].'%';
                } else {
                    $currencyCode = isset($item['base_currency_code']) ? $item['base_currency_code'] : null;
                    $item[$this->getData('name')] = $this->_priceFormatter->format(
                        $item[$this->getData('name')],
                        false,
                        null,
                        null,
                        $currencyCode
                    );
                }
            }
        }

        return $dataSource;
    }
}
