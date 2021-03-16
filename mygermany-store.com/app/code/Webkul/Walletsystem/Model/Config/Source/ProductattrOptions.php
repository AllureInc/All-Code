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

namespace Webkul\Walletsystem\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
use Webkul\Walletsystem\Model\Walletcreditrules;

class ProductattrOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        $this->_options=[
            [
                'label'=>'Select Options',
                'value'=>''
            ],[
                'label'=>__('Product Credit Amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_PRODUCT
            ],[
                'label'=>__('Rules amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_RULE
            ]
        ];
        return $this->_options;
    }
    /**
     * Get a text for option value
     * @param  array
     * @return string
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
    /**
     * Retrieve flat column definition
     * @return array
     */
    
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}
