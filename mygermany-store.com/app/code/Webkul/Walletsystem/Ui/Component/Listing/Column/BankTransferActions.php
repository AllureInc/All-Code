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
use Magento\Framework\UrlInterface;
 
class BankTransferActions extends Column
{
    /** Url path */
    const VIEWTRANSACTIONPATH = 'walletsystem/wallet/view';
    const BANKTRANSFERUPDATEPATH = 'walletsystem/wallet/banktransfer';
    const BANKTRANSFERCANCELPATH = 'walletsystem/wallet/disapprove';
 
    /** @var UrlInterface */
    protected $_urlBuilder;
 
    /**
     * @var string
     */
    private $_editUrl;
 
    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param [type]             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $viewUrl = self::VIEWTRANSACTIONPATH,
        $cancelUrl = self::BANKTRANSFERCANCELPATH
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->viewUrl = $viewUrl;
        $this->cancelUrl = $cancelUrl;
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
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['view'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->viewUrl,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __("View Transaction")
                    ];
                    $item[$name]['cancel'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->cancelUrl,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __("Cancel Transaction")
                    ];
                    $item[$name]['update'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            self::BANKTRANSFERUPDATEPATH,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __("Approve Transaction Status"),
                        'confirm' => [
                            'title' => __("Approve Transaction Status"),
                            'message' => __("Are you sure to update status of Transaction?")
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
