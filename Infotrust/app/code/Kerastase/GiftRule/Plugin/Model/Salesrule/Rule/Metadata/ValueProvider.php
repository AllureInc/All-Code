<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Plugin\Model\Salesrule\Rule\Metadata;

class ValueProvider
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        array $result
    ) {
        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
            'label' => __('Buy X & Get Y (Free Gift per Cart)'),
            'value' => \Kerastase\GiftRule\Helper\Data::BUY_X_GET_N_FREE_RULE
        ];
        return $result;
    }
}
