<?php
/**
 * Copyright © Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\VendorAttribute\Ui\DataProvider;

class Attributes extends \Magento\ConfigurableProduct\Ui\DataProvider\Attributes
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;
    protected $customerSession;
    protected $vendorAttrModel;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
        \Magento\Customer\Model\Session $customerSession,
        \Mangoit\VendorAttribute\Model\Attributemodel $vendorAttrModel,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $configurableAttributeHandler, $meta, $data);
        $this->customerSession = $customerSession;
        $this->vendorAttrModel = $vendorAttrModel;
    }

    public function getData()
    {
        $items = [];

        //Load product by product id
        $customerId = $this->customerSession->getCustomer()->getId();
        $vendorIdsExcept = [$customerId, 0];
        $vendorAttrColl = $this->vendorAttrModel->getCollection()
        ->addFieldToFilter('vendor_id',['nin' => $vendorIdsExcept]);

        $attributeCodes= [];
        foreach ($vendorAttrColl as $productKey => $productValue) {
            $attributeCodes[] = $productValue->getAttributeCode();  
        }

        $attributesToExclude = $this->configurableAttributeHandler->getApplicableAttributes()
                ->addFieldToFilter(
                    'apply_to',
                    [
                        'in' => [
                            'simple',
                            'configurable',
                            'simple,configurable'
                        ]
                    ])
                ->getData();

        $attributesToExclude = array_column($attributesToExclude, 'attribute_code');
        $attributesToExclude = array_merge($attributesToExclude, $attributeCodes);

        $this->getCollection()->addFieldToFilter('attribute_code', ['nin' => $attributesToExclude]);

        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            // if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)  && (!in_array($attribute->getAttributeCode(), $attributeCodes))) {
                $items[] = $attribute->toArray();
            // } else {
            //     $skippedItems++;
            // }
        }
        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }
}
