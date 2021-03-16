<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Plugin CLass
 */

namespace Cnnb\WhatsappApi\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as CnnbLayoutProcessor;
use Cnnb\WhatsappApi\Helper\Data;

class LayoutProcessor
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * LayoutProcessor constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param CnnbLayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(CnnbLayoutProcessor $subject, $jsLayout)
    {

        if (!$this->helper->isModuleEnabled()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            ['telephone'] = $this->helper->telephoneFieldConfig("shippingAddress");
        }

        /* config: checkout/options/display_billing_address_on = payment_method */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])) {

            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                     ['payment']['children']['payments-list']['children'] as $key => $payment) {

                $method = substr($key, 0, -5);

                /* telephone */
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['telephone'] = $this->helper->telephoneFieldConfig("billingAddress", $method);
            }
        }

        /* config: checkout/options/display_billing_address_on = payment_page */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form'])) {

            $method = 'shared';

            /* telephone */
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
            ['telephone'] = $this->helper->telephoneFieldConfig("billingAddress", $method);
        }

        return $jsLayout;
    }
}
