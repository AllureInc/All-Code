<?php
/**
 * Sparsh_SalesEmailAttachments
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SalesEmailAttachments\Model\Config\Source;

/**
 * Class AttachTermsConditons
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class AttachTermsConditons implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'order', 'label' => __('Order')],
            ['value' => 'invoice', 'label' => __('Invoice')],
            ['value' => 'shipment', 'label' => __('Shipment')],
            ['value' => 'creditmemo', 'label' => __('Credit Memo')]
        ];
    }
}
