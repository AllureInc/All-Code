<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class AdsPurchaseDetail extends \Magento\Framework\Model\AbstractModel
{
    /**
     * mobikul api cache tag
     */
    const CACHE_TAG = 'marketplace_ads_purchase_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_ads_purchase_details';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_ads_purchase_details';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAdvertisementManager\Model\ResourceModel\AdsPurchaseDetail');
    }
}
