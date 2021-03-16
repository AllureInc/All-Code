<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetSellerName extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'ADMIN';

    /**
     * object of store manger class
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * @param ContextInterface      $context
     * @param UiComponentFactory    $uiComponentFactory
     * @param StoreManagerInterface $storemanager
     * @param array                 $components
     * @param array                 $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storemanager,
        \Magento\Customer\Model\Customer $customer,
        array $components = [],
        array $data = []
    ) {
        $this->_storeManager = $storemanager;
        $this->_customer = $customer;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$fieldName]) {
                    $name = $this->_customer->load($item[$fieldName])->getName();
                    $item[$fieldName] = $name;
                } else {
                    $item[$fieldName] = self::NAME;
                }
            }
        }
        return $dataSource;
    }
}
