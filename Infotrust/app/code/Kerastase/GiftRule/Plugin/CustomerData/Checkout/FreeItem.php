<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 */
namespace Kerastase\GiftRule\Plugin\CustomerData\Checkout;

class FreeItem
{
    /**
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $object
     * @return mixed
     */
    public function aroundGetItemData(
        \Magento\Checkout\CustomerData\DefaultItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $object
    ) {
        $result = $proceed($object);
        
        //Tweak the attribute logic
        $isVisibleInSiteVisibility  = $result['is_visible_in_site_visibility'];
        $isFreeProduct              = $object->getIsFreeProduct();
        $isItemEditable             = $isVisibleInSiteVisibility && !$isFreeProduct;
        $result['is_visible_in_site_visibility'] = $isItemEditable;
        $result['is_free_gift'] = $isFreeProduct;
        $result['free_gift_label'] = '';
        if ($isFreeProduct) {
            // $object->getBuyRequest()->getData('free_gift_label') vs $object->getFreeGiftLabel()
            $result['free_gift_label'] = $object->getData('free_gift_label') ?: __('Free Gift');
        }
        
        return $result;
    }
}
