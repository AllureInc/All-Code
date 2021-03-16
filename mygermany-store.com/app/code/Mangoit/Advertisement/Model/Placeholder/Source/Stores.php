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

namespace Mangoit\Advertisement\Model\Placeholder\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Positions.
 */
class Stores implements OptionSourceInterface
{

    /**
     * Get positions.
     *
     * @return array
     */
    public function getStoresNames()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeBlock = $objectManager->create('Mangoit\Advertisement\Block\Adminhtml\Newad');
        $storCollection = $storeBlock->getAllStoreList();
        return $storCollection;
    }
    public function toOptionArray()
    {
        $options = [];
        $storesName = $this->getStoresNames();
        foreach ($storesName as $store) {
            if (($store['group_id'] >= 1) && ($store['is_active'] == 1)) {
                /*echo "<br> store name : ".$store['name'];*/
                $options[] = [
                    'label' => __($store['name']),
                    'value' => $store['name']
                ];
            }
        }
        /*$options = [
            [
                'label' => __("Image"),
                'value' => 1
            ],
            [
                'label' => __("Product"),
                'value' => 2,
            ],
            [
                'label' => __("Category"),
                'value' => 3,
            ],
            [
                'label' => __("HTML Editor"),
                'value' => 4,
            ]
        ];*/
        return $options;
    }
}
