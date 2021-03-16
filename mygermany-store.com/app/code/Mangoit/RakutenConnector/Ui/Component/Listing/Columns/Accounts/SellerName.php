<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Ui\Component\Listing\Columns\Accounts;

class SellerName implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Mangoit\RakutenConnector\Model\Config\Source\MarketplaceSellers $sellers
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
