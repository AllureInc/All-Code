<?php

namespace Webkul\MpAmazonConnector\Ui\Component\Listing\Columns\Accounts;

class Marketplace implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Webkul\MpAmazonConnector\Model\Config\Source\AmazonMarketplace $amazonMarketplace
    ) {
        $this->amazonMarketplace = $amazonMarketplace;
    }
    /**
     * Options getter.
     *
     * @return array
     */

    public function toOptionArray()
    {
        $amzMp = [];
        $marketplace = $this->amazonMarketplace->toArray();
        foreach ($marketplace as $marketplaceCode => $label) {
            $amzMp[] = [
                'value' => $marketplaceCode,
                'label' => __($label)
            ];
        }
        return $amzMp;
    }
}
