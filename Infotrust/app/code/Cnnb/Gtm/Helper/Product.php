<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper Class
 * For providing the product image url
 */
namespace Cnnb\Gtm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

/**
 * Product Helper Class
 */
class Product extends AbstractHelper
{
    public function getImageUrl($product)
    {
        /** @var $product ProductInterface */
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
            . 'catalog/product' . ($product->getData('image') ?: $product->getData('small_image'));
    }
}
