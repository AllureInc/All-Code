<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For retrieving order data
 */

namespace Cnnb\Gtm\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\Model\DataLayerEvent;

/**
 * @method Array setOrderIds(Array $orderIds)
 * @method Array getOrderIds()
 */
class Order extends Template
{
    /**
     * Add product data to datalayer
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();

        $data = [
            'event' => DataLayerEvent::ORDER_SUCCESS_PAGE_EVENT
        ];

        $tm->addCustomDataLayerByEvent(DataLayerEvent::ORDER_SUCCESS_PAGE_EVENT, $data);

        return $this;
    }
}
