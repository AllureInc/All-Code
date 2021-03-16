<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPushNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPushNotification\Block;

use Magento\Framework\View\Element\Template\Context;

class PushHome extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpPushNotification\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        \Webkul\MpPushNotification\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
    }
    
    /**
     * get secure url
     *
     * @return string
     */
    public function getSecureUrl()
    {
        return $this->helper->getSecureUrl();
    }

    /**
     * get sender id
     *
     * @return string
     */
    public function getSenderId()
    {
        return $this->helper->getSenderId();
    }

     /**
      * get sender key
      *
      * @return string
      */
    public function getServerKey()
    {
        return $this->helper->getServerKey();
    }
}
