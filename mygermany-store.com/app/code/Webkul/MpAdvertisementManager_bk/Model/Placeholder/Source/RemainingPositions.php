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

namespace Webkul\MpAdvertisementManager\Model\Placeholder\Source;

use Webkul\MpAdvertisementManager\Model\Placeholder\Source\Positions as Positions;
use Magento\Framework\App\RequestInterface;

/**
 * Class RemainingPositions.
 */
class RemainingPositions extends Positions
{
    public function __construct(
        \Webkul\MpAdvertisementManager\Model\PricingFactory $pricing,
        RequestInterface $requestInterface
    ) {
        $this->_pricing = $pricing;
        $this->_request = $requestInterface;
    }
    /**
     * Get RemainingPositions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }

    public function getOptionArray()
    {
        $options = [
            [
                'label' => "Please select a position",
                'value' => null
            ],
            [
                'label' => __("Home Seller Ads Page Top"),
                'value' => 1,
            ],
            [
                'label' => __("Home Seller Popup Ads"),
                'value' => 2,
            ],
            [
                'label' => __("Home Seller Ads Page Bottom Container"),
                'value' => 3,
            ],
            [
                'label' => "Category Seller Ads Page Top",
                'value' => 4,
            ],
            [
                'label' => "Category Seller Ads Page Bottom Container",
                'value' => 5,
            ],
            [
                'label' => "Category Seller Ads Main",
                'value' => 6,
            ],
            [
                'label' => "Category Seller Ads Div Sidebar Main Before",
                'value' => 7,
            ],
            [
                'label' => "Category Seller Ads Div Sidebar Main After",
                'value' => 8,
            ],
            [
                'label' => "Catalog Product Seller Ads Page Top",
                'value' => 9,
            ],
            [
                'label' => "Catalog Product Seller Ads Page Bottom Container",
                'value' => 10,
            ],
            [
                'label' => "Home Seller Ads Product Main Info",
                'value' => 11,
            ],
            [
                'label' => "Catalog Search Seller Ads Page Top",
                'value' => 12,
            ],
            [
                'label' => "Catalog Search Seller Ads Page Bottom Container",
                'value' => 13,
            ],
            [
                'label' => "Catalog Search Seller Ads Main",
                'value' => 14,
            ],
            [
                'label' => "Catalog Search Seller Ads div Sidebar Main Before",
                'value' => 15,
            ],
            [
                'label' => "Catalog Search Seller Ads div Sidebar Main After",
                'value' => 16,
            ],
            [
                'label' => "Checkout cart Seller Ads Page Top",
                'value' => 17,
            ],
            [
                'label' => "Checkout cart Seller Ads Page bottom Container",
                'value' => 18,
            ],
            [
                'label' => "Checkout Seller Ads Page Top",
                'value' => 19,
            ],
            [
                'label' => "Checkout Seller Ads Page bottom Container",
                'value' => 20,
            ]
        ];
        $post = $this->_request->getParams();
        if (!(isset($post['id']))) {
            $collection = $this->_pricing->create()->getCollection();
            foreach ($collection as $model) {
                if ($model->getBlockPosition()) {
                    if (array_key_exists($model->getBlockPosition(), $options)) {
                        unset($options[$model->getBlockPosition()]);
                    }
                }
            }
        }
        return $options;
    }
}
