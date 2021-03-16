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
        RequestInterface $requestInterface,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper
    ) {
        $this->_pricing = $pricing;
        $this->_request = $requestInterface;
        $this->_adsHelper = $adsHelper;
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
        $options = $this->_adsHelper->getAllAdsPositions();
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
        if (count($options) == 1 && $options[0]['label'] == __('Please select a position')) {
            $options[0]['label'] = __('All the block pricing have already been set.');
        }
        return $options;
    }
}
