<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Advertisement\Helper\Webkul;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;

/**
 * Webkul MpAdvertisementManager Helper Order.
 */
class Order extends \Webkul\MpAdvertisementManager\Helper\Order
{
    /**
     * [__construct
     *
     * @param \Magento\Framework\App\Helper\Context                           $context
     * @param \Magento\Config\Model\Config\Backend\Encrypted                  $encrypted
     * @param \Magento\Framework\Session\SessionManagerInterface              $sessionManager
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param DateTime                                                        $date
     * @param \Magento\Config\Model\ResourceModel\Config                      $resourceConfig
     * @param \Magento\Framework\Json\Helper\Data                             $jsonHelper
     * @param \Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory $pricingDataFactory
     * @param \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface   $pricingRepository
     * @param \Webkul\MpAdvertisementManager\Helper\Data                      $helper
     * @param \Magento\Sales\Model\OrderFactory                               $orderFactory
     * @param \Magento\Framework\ObjectManagerInterface                       $objectManager
     * @param Session                                                         $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DateTime $date,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory $pricingDataFactory,
        \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface $pricingRepository,
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Session $customerSession
    ) {
        parent::__construct(
                $context,
                $encrypted,
                $sessionManager,
                $storeManager,
                $date,
                $resourceConfig,
                $jsonHelper,
                $pricingDataFactory,
                $pricingRepository,
                $helper,
                $orderFactory,
                $objectManager,
                $customerSession
            );
    }

    /**
     * getSellerAds get seller ad position wise
     *
     * @param $customerIds array of customer ids
     * @param $position int position id
     *
     * @return array ads data
     */
    public function getSellerAds($customerIds, $position, $orderDetail = 0)
    {
        $params = $this->_getRequest()->getParams();
        $ads = [];
        $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');

        if((isset($params['is_admin']) && $params['is_admin'] == 1) &&
            (isset($params['block']) && $params['block'] != '')) {
            $blockId = base64_decode(urldecode($params['block']));
            $adsDatas = $model->create()->getCollection()
                ->addFieldToFilter('id', ['eq' => $blockId])
                ->addFieldToFilter('block_position', ['eq' => $position])
                ->addFieldToFilter('seller_id', ['in' => $customerIds])
                ->addFieldToFilter('store_id', $this->_helper->getCurrentStoreId());

            foreach ($adsDatas as $adsData) {
                $ads[$adsData['item_id']] = ['block_position' => $adsData['block_position'], 'price' => $adsData['price'], 'days' => $adsData['valid_for'], 'block' => $adsData['block']];
            }
        } elseif (!isset($params['is_admin'])) {
            $adsDatas = $model->create()->getCollection()
                ->addFieldToFilter('block_position', ['eq' => $position])
                ->addFieldToFilter('seller_id', ['in' => $customerIds])
                ->addFieldToFilter('invoice_generated', ['eq' => 1])
                ->addFieldToFilter('enable', ['eq' => 1])
                ->addFieldToFilter('mis_approval_status', ['eq' => 1])
                ->addFieldToFilter('store_id', $this->_helper->getCurrentStoreId());

            foreach ($adsDatas as $adsData) {
                // $validity = $this->getInvoceItemValidity($adsData['order_id'], $adsData['item_id'], $adsData['valid_for']);
                $validity = $this->validateDate($adsData['mis_approval_date'], $adsData['valid_for']);
                if ($validity) {
                    $ads[$adsData['item_id']] = ['block_position' => $adsData['block_position'], 'price' => $adsData['price'], 'days' => $adsData['valid_for'], 'block' => $adsData['block']];
                }
            }
        }
        return $ads;
    }

    /**
     * getCountOfSellerAds get count of seller ads according to the position
     *
     * @param $customerIds array of customer ids
     * @param $position int position id
     * @return int
     */
    public function getCountOfSellerAds($customerIds, $position)
    {
        $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');
        $adsDatas = $model->create()->getCollection()
                        ->addFieldToFilter('block_position', ['eq' => $position])
                        ->addFieldToFilter('seller_id', ['in' => $customerIds])
                        ->addFieldToFilter('mis_approval_status', ['neq' => 2])
                        ->addFieldToFilter('store_id', $this->_helper->getCurrentStoreId());
        $ads = [];
        foreach ($adsDatas as $adsData) {
            $validity = $this->validateDate($adsData['created_at'], $adsData['valid_for']);
            if ($validity) {
                $ads[$adsData['item_id']] =['block_position'=>$adsData['block_position'], 'price'=>$adsData['price'], 'days'=>$adsData['valid_for'],
                'block'=>$adsData['block']];
            }
        }
        return $ads;
    }
}
