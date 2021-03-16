<?php
/**
 * Mangoit Marketplace system Helper Data
 *
 * @category  Mangoit
 * @package   Mangoit_Mpreportsystem
 */
namespace Mangoit\Marketplace\Helper;

use Webkul\Mpreportsystem\Model\Product as SellerProduct;
use Magento\Sales\Model\ResourceModel\Order;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Webkul\Marketplace\Model\ResourceModel;

/**
 * Mangoit Marketplace Helper Data.
 */
class Reportsystemdata extends \Webkul\Mpreportsystem\Helper\Data
{
    // get all order status
    public function getOrderStatus()
    {
        $orderStatusUpdatedArray = [];
        $orderStatusArray = $this->_salesStatusCollection
            ->create()->toOptionArray();
        foreach ($orderStatusArray as $key => $orderStatus) {
            // if ($orderStatus['value']!='closed') {
                $orderStatusUpdatedArray[$orderStatus['value']] =
                $orderStatus['label'];
            // }
        }
        return $orderStatusUpdatedArray;
    }
}
