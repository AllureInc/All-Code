<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    const EXPIRED = 'expired';

    /**
     * @var \Webkul\MpSellerCoupons\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface
     */
    protected $_mpSellerCouponsRepository;

    /**
     * object of store manger class
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * @param \Webkul\MpSellerCoupons\Helper\Data                            $dataHelper
     * @param \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository
     * @param ContextInterface                                               $context
     * @param UiComponentFactory                                             $uiComponentFactory
     * @param StoreManagerInterface                                          $storemanager
     * @param array                                                          $components
     * @param array                                                          $data
     */
    public function __construct(
        \Webkul\MpSellerCoupons\Helper\Data $dataHelper,
        \Webkul\MpSellerCoupons\Api\MpSellerCouponsRepositoryInterface $mpSellerCouponsRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storemanager,
        array $components = [],
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_mpSellerCouponsRepository = $mpSellerCouponsRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storemanager;
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
                if ($item['status']!='used') {
                    $coupon = $this->_mpSellerCouponsRepository
                                ->getCouponByEntityId($item['entity_id']);

                    $expireDate = $this->_dataHelper
                                ->getDateTimeAccordingTimeZone($coupon->getExpireAt());
                    $currentDate = $this->_dataHelper->getCurrentDate();

                    if (strtotime($currentDate) > strtotime($expireDate)) {
                        $item['status'] = self::EXPIRED;
                    }
                }
            }
        }
        return $dataSource;
    }
}
