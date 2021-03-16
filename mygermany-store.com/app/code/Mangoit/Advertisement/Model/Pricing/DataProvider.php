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

namespace Mangoit\Advertisement\Model\Pricing;

use Magento\Eav\Model\Config;
use Webkul\MpAdvertisementManager\Model\Pricing;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\Collection;
use Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory as PricingCollectionFactory;

/**
 * Class DataProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Webkul\MpAdvertisementManager\Model\Pricing\DataProvider
{

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                'Magento\Framework\Session\SessionManagerInterface'
            );
        }

        return $this->session;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /**
 * @var Customer $pricing
*/
        // echo "<pre>";
        foreach ($items as $pricing) {
            $result['ads_pricing'] = $pricing->getData();
            $this->loadedData[$pricing->getId()] = $result;
            $this->loadedData[$pricing->getId()]['mis_adv_content']['content_type'] = $result['ads_pricing']['content_type'];
            $this->loadedData[$pricing->getId()]['mis_adv_content']['ad_type'] = $result['ads_pricing']['ad_type'];
            // print_r($this->loadedData);
        }

        $data = $this->getSession()->getPricingFormData();

        if (!empty($data)) {
            $pricingId = isset($data['ads_pricing']['id'])
            ? $data['ads_pricing']['id'] : null;
            $this->loadedData[$pricingId] = $data;
            $this->getSession()->unsPricingFormData();
        }
        
        return $this->loadedData;
    }
}
