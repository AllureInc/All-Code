<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Helper;

class Config extends \Webkul\MarketplacePreorder\Helper\Data
{
    public function getPreorderConfigData($productId)
    {
        $config['product_'.'0'] = [
            'id' => 0,
            'preorder' => 0,
            'config' => 0,
            'configmsg' => "",
            'msg' => "",
            'payHtml' => ""
        ];
        $isProduct = false;
        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);
        $collection->addAttributeToSelect('*');
        foreach ($collection as $item) {
            $product = $item;
            $isProduct = true;
            break;
        }
        if ($isProduct) {
            $productType = $product->getTypeId();
            if ($productType == 'configurable') {
                $configModel = $this->_configurable;
                $usedProductIds = $configModel->getUsedProductIds($product);
                foreach ($usedProductIds as $usedProductId) {
                    if ($this->isPreorder($usedProductId)) {
                        $configMsg = __('Product has preorder option(s)');
                        $html = "<div class='wk-config-msg-box wk-info'>";
                        $html .= $configMsg;
                        $html .= '</div>';
                        $configMsg = $html;
                        $config['product_'.$usedProductId] = [
                            'id' => $usedProductId,
                            'preorder' => 1,
                            'config' => 1,
                            'configmsg' => $configMsg,
                            'msg' => $this->getPreOrderInfoBlock($usedProductId),
                            'payHtml' => $this->getPayPreOrderHtml($usedProductId)
                        ];
                    }
                }
            }
        }
        return $config;
    }
}
