<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing checkout data
 */

namespace Cnnb\Gtm\Block\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\Model\Cart as CartModel;
use Magento\Customer\Model\Session;
use Cnnb\Gtm\Model\DataLayerEvent;

class Checkout extends Template
{
    /**
     * @var CartModel
     */
    protected $_cartModel;

    /**
     * @var session
     */
    protected $_session;

    /**
     * @param Context $context
     * @param CartModel $cartModel
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        CartModel $cartModel,
        Session $session,
        $data = []
    ) {
        $this->_cartModel = $cartModel;
        $this->_session = $session;
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
        $session_data = $this->_session->getData();
        if (isset($session_data['visitor_data']) && isset($session_data['visitor_data']['do_customer_login'])) {
            $data = [
                'event' => DataLayerEvent::CHECKOUT_PAGE_EVENT,
                'cart' => $this->_cartModel->getCart()
            ];

            $tm->addVariable('list', 'checkout');
            $tm->addCustomDataLayerByEvent(DataLayerEvent::CHECKOUT_PAGE_EVENT, $data);
        }
        
        return $this;
    }
}
