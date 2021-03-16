<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For getting cart data
 */
namespace Cnnb\Gtm\Block\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\Model\Cart as GtmCartModel;
use Cnnb\Gtm\Model\DataLayerEvent;

class Cart extends Template
{

    /**
     * @var GtmCartModel
     */
    protected $_gtmCart;

    /**
     * @param Context $context
     * @param GtmCartModel $gtmCart
     * @param array $data
     */
    public function __construct(
        Context $context,
        GtmCartModel $gtmCart,
        $data = []
    ) {
        $this->_gtmCart = $gtmCart;
        parent::__construct($context, $data);
    }

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
            'event' => "uaevent",
            'cart' => $this->_gtmCart->getCart()
        ];

        $tm->addVariable('list', 'cart');
        // $tm->addCustomDataLayerByEvent(DataLayerEvent::CART_PAGE_EVENT, $data);
        $tm->addCustomDataLayerByEvent('uaevent', $data);
        return $this;
    }
}
