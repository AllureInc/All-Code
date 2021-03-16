<?php

namespace Mangoit\VendorAttribute\Block\Adminhtml; 

use Magento\Ui\Component\MassAction\Filter;
use Mangoit\VendorAttribute\Model\ResourceModel\Attributemodel\CollectionFactory;
/**
* 
*/
class Makeglobal extends \Magento\Framework\View\Element\Template
{
	
	protected $newObjectManager;
	protected $filter;
    protected $eavConfig;
	protected $collectionFactory;

	public function __construct(
		Filter $filter,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig
		)
	{
		$this->newObjectManager = $objectmanager;
		$this->filter = $filter;
        $this->_eavConfig = $eavConfig;
		$this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}
   
	public function getformKey()
    {
    	$FormKey = $this->newObjectManager->get('Magento\Framework\Data\Form\FormKey');
    	return $FormKey->getFormKey();
    }

    public function getAttributesList()
    {
    	$typeArray = [];
    	$collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection->getData() as $key => $value) {
        	array_push($typeArray,$value['attribute_type']);
        }
        return $typeArray;
    }

    public function getAttributesCodeList()
    {
        $attrCodeList = [];

        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection->getData() as $key => $value) {
            array_push($attrCodeList,$value['attribute_code']);
        }
        return $attrCodeList;
    }

    public function getAttributesIdList()
    {
        $attrIdList = [];

        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection->getData() as $key => $value) {
            array_push($attrIdList,$value['attribute_id']);
        }
        return $attrIdList;
    }

    public function getAttributesLabelList()
    {
        $attrLabel = [];

        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection->getData() as $key => $value) {
            array_push($attrLabel,$value['attribute_label']);
        }
        return $attrLabel;
    }

    public function getSaveUrl()
    {
    //replace the tab with the url you want
        return $this->getUrl('vendorattribute/attribute/saveattribute');
    }

    public function getAttributeCollection($attributeCode)
    {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        return $options;
    }
}