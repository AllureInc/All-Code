<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Plugin\Email\Model\Source;

class Variables
{
    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    private $additionalConfigVariables = [];
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->additionalConfigVariables = [
            [
                'value' => 'walletsystem/prefixfortransaction/prefix_admin_credit',
                'label' => __('Wallet Admin credit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/prefix_admin_debit',
                'label' => __('Wallet Admin debit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/prefix_customer_credit',
                'label' => __('Wallet Customer credit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/prefix_customer_debit',
                'label' => __('Wallet Customer debit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/prefix_order_credit',
                'label' => __('Wallet Order credit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/prefix_order_debit',
                'label' => __('Wallet Order debit')
            ], [
                'value' => 'walletsystem/prefixfortransaction/cashback_prefix',
                'label' => __('Wallet cashback')
            ], [
                'value' => 'walletsystem/prefixfortransaction/refund_order_amount',
                'label' => __('Wallet refund order amount')
            ], [
                'value' => 'walletsystem/prefixfortransaction/refund_wallet_order',
                'label' => __('Wallet amount order refund')
            ], [
                'value' => 'walletsystem/prefixfortransaction/bank_transfer_amount',
                'label' => __('Wallet amount transferred to bank')
            ]
        ];
    }
    /**
     * Returns additional config config variables
     *
     * @return array
     */
    public function getAdditionalConfigVariables()
    {
        return $this->additionalConfigVariables;
    }
    /**
     * Return available config variables
     *
     * @param \Magento\Email\Model\Source\Variables $subject
     * @param array $result
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function afterGetData(\Magento\Email\Model\Source\Variables $subject, $result)
    {
        return array_merge($result, $this->getAdditionalConfigVariables());
    }
}
