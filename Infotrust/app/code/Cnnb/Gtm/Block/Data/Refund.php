<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing refund's data
 */
namespace Cnnb\Gtm\Block\Data;

use Cnnb\Gtm\Helper\Data as Helper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\Model\DataLayerEvent;

class Refund extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var helper
     */
    private $_helper;

    public function __construct(
        Context $context,
        Registry $registry,
        Helper $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Add refund data to datalayer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();
        if ($this->_helper->isPartialRefundEnabled() != 0) {
            $order_type = 'Partial Refund';
        } else {
            $order_type = 'Refund';
        }

        $data = [
            'event' => 'refund',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> $order_type
        ];
        $tm->addVariable('list', 'order');
        $tm->addCustomDataLayerByEvent(DataLayerEvent::ORDER_PAGE_EVENT, $data);

        return $this;
    }
}
