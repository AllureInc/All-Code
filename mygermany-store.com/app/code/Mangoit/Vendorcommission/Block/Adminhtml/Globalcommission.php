<?php 

namespace Mangoit\Vendorcommission\Block\Adminhtml;

/**
* 
*/
class Globalcommission extends \Magento\Framework\View\Element\Template
{
	protected $newObjectManager;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager
		)
	{
		$this->newObjectManager = $objectmanager;
		parent::__construct($context);
	}

	public function sayHello()
	{
		return __('Hello World');
	}
     
    public function getAttributeData()
    {
        $entityAttributeCollection = $this->newObjectManager->get('Mangoit\Vendorcommission\Helper\Data');
        $myData = $entityAttributeCollection->getCustomAttributeOption();
        $attrArray = array();
        foreach ($myData as $label => $value) {
            $attrArray[$value['value']] = $value['label'];
        }
        return $attrArray;
    }

    public function getCommissionValuesFromStore(){
        $storeValue = $this->newObjectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $rangeArray = $storeValue->getValue('marketplace/commission_setting/ranges');
        return trim($rangeArray);
    }

    public function getCommissionValues()
    {
        $commissionData = $this->newObjectManager->create('Mangoit\Vendorcommission\Model\Turnover');
        $commissionCollection = $commissionData->getCollection();
        return $commissionCollection;
    }

    public function getSaveUrl()
    {
    //replace the tab with the url you want
        return $this->getUrl('vendorcommission/globalcommission/save');
    }
    public function getformKey()
    {
    	$FormKey = $this->newObjectManager->get('Magento\Framework\Data\Form\FormKey');
    	return $FormKey->getFormKey();
    }

    public function getSerializedData()
    {
        $commissionData = $this->newObjectManager->create('Mangoit\Vendorcommission\Model\Turnover');
        $serializedData = $commissionData->load(1)->getCommissionRule();
        $unserializedData = unserialize($serializedData);
        return $unserializedData;
    }

    public function getFieldValue($commissionRuleArray, $attrValue, $value)
    {
        $commission = [];
        $fieldValue = 0;
        foreach ($commissionRuleArray as $key => $data) {
            if ($key == $attrValue) {
                foreach ($data as $newkey => $newvalue) {
                    if ($newkey == $value) {
                        $fieldValue = $newvalue;
                    }
                }
            }
        }
        return $fieldValue;
    }

    public function getCurrencySymbol()
    {
        $newStoreManager =  $this->newObjectManager->create('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $newStoreManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->newObjectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }
}