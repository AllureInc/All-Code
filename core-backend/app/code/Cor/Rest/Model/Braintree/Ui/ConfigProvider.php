<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cor\Rest\Model\Braintree\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Cor\Rest\Api\BraintreeConfigProviderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider extends \Magento\Braintree\Model\Ui\ConfigProvider implements BraintreeConfigProviderInterface
{

}
