<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for main panel of product page
 *
 * @api
 * @since 101.0.0
 */
class WalletCashBack extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeMinQtyField($meta);

        return $meta;
    }
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * Customize Weight filed
     *
     * @param array $meta
     * @return array
     * @since 101.0.0
     */
    protected function customizeMinQtyField(array $meta)
    {
        $cashBackPath = $this->arrayManager->findPath('wallet_cash_back', $meta, null, 'children');
        if ($cashBackPath) {
            $meta = $this->arrayManager->merge(
                $cashBackPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataScope' => 'wallet_cash_back',
                    'validation' => [
                        'required-entry' => true,
                        'validate-zero-or-greater' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'component' => 'Webkul_Walletsystem/js/form/element/is-special',
                    'imports' => [
                        'handleChanges' => '${ $.provider }:data.product.wallet_credit_based_on'
                    ]
                ]
            );
        }
        return $meta;
    }
}
