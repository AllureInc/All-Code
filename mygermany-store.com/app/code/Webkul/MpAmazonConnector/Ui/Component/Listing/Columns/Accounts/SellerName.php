<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Ui\Component\Listing\Columns\Accounts;

class SellerName implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Webkul\MpAmazonConnector\Model\Config\Source\MarketplaceSellers $sellers
    ) {
        $this->sellers = $sellers;
    }
    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        return $this->sellers->getSellers();
    }
}
