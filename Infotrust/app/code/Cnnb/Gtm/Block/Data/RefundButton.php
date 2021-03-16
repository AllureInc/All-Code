<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing refund's button data
 */
namespace Cnnb\Gtm\Block\Data;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Helper\Data as GtmHelper;
use Cnnb\Gtm\Block\DataLayerAbstract;

class RefundButton extends Template
{
    /**
     * @var GtmHelper
     */
    protected $_gtmHelper;

    /**
     * @var DataLayerAbstract
     */
    protected $_dataLayer;

    public function __construct(
        Context $context,
        GtmHelper $gtmHelper,
        DataLayerAbstract $dataLayerAbstract,
        array $data = []
    ) {
        $this->_gtmHelper = $gtmHelper;
        $this->_dataLayer = $dataLayerAbstract;
        parent::__construct($context, $data);
    }

    /**
     * get current order's data
     *
     * @return object
     */
    public function getCurrentOrderData()
    {
        return $this->_dataLayer->getCurrentOrderData();
    }
}
