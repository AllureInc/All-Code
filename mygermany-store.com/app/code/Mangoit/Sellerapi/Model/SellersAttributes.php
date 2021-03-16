<?php 
namespace Mangoit\Sellerapi\Model;

use Mangoit\Sellerapi\Api\SellersAttributesInterface; 
use Magento\Framework\Webapi\Exception;

class SellersAttributes implements SellersAttributesInterface {

	protected $resultJsonFactory;
	protected $_block;
	protected $_webkulHelper;
	protected $_storeManager;
	protected $_session;
	protected $_customer;
	protected $_sellersAttributes;
	protected $_sellerProduct;

	public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Mangoit\Sellerapi\Block\Seller\View $block,
		\Webkul\Marketplace\Helper\Data $webkulHelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Mangoit\VendorAttribute\Model\Attributemodel $sellersAttributes,
		\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
		\Mangoit\Sellerapi\Model\SellerProduct $sellerProduct,
		\Magento\Customer\Model\Customer $customer
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_block = $block;
        $this->_webkulHelper = $webkulHelper;
        $this->_storeManager = $storeManager;
        $this->_sellersAttributes = $sellersAttributes;
        $this->_attributeFactory = $attributeFactory;
        $this->_customer = $customer;
        $this->_sellerProduct = $sellerProduct;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getSellerAttributes($seller_id)
	{
		$token = $this->_sellerProduct->getBearerToken();
		$restricted_attributes = ['msrp_display_actual_price_type', 'gift_message_available', 'fsk_product_type', 'delivery_days_to', 'delivery_days_from', 'wallet_credit_based_on', 'visibility', 'status', 'shipment_type', 'restricted_product', 'quantity_and_stock_status', 'msrp_display_actual_price_type', 'custom_layout', 'custom_design_to', 'custom_design_from', 'custom_design', 'price_view', 'tax_class_id', 'options_container', 'page_layout'];
		$unused_attributes = ['custom_layout', 'custom_design_to', 'custom_design_from', 'custom_design', 'price_view', 'options_container', 'page_layout', 'din'];
		$attribute_array = [];
		$seller_attribute_array = [];
		$seller_id_array = ['0', $seller_id];
		$sellers_attr = $this->_sellersAttributes->getCollection()->addFieldToFilter('vendor_id', array('nin'=> $seller_id_array))->addFieldToSelect('attribute_code');
		foreach ($sellers_attr as $key => $value) {
			$seller_attribute_array[] = $value['attribute_code'];
		}
		/*print_r($seller_attribute_array);
		die('...');*/
		$attributeInfo = $this->_attributeFactory->getCollection()->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);

		$customer = $this->_customer->load($seller_id);
		if ($customer->getSellerApiToken() && ($customer->getSellerApiToken() == $token)) {
			foreach($attributeInfo as $attributes) {
				if (
					!in_array($attributes->getAttributeCode(), $unused_attributes) &&
					!in_array($attributes->getAttributeCode(), $seller_attribute_array)
				) {
					$attribute_array[$attributes->getAttributeCode()]['attribute_code'] = $attributes->getAttributeCode();
					$attribute_array[$attributes->getAttributeCode()]['attribute_name'] = $attributes->getName();
					$attribute_array[$attributes->getAttributeCode()]['attribute_type'] = $attributes->getFrontendInput();
	                if ($attributes->getFrontendInput() == 'select') {
	                    foreach ($attributes->getOptions() as $option) {
	                    	if ($option->getLabel() != '' && strlen(trim($option->getLabel())) > 0 && $option->getLabel() != null)  {
	                    		$select_label = ''.$option->getLabel();
	                    		$select_value = ''.$option->getValue();

	                    		$attribute_array[$attributes->getAttributeCode()]['attribute_options'][] = ['label'=> $select_label, 'value_id'=> $select_value];
	                    	}
	                    }
	                }
	                if ($attributes->getFrontendInput() == 'multiselect') {
	                	if ($option->getLabel() != '' && strlen(trim($option->getLabel())) > 0 && $option->getLabel() != null)  {
	                    		$multi_select_label = ''.$option->getLabel();
	                    		$multi_select_value = ''.$option->getValue();
	                    		
	                    		$attribute_array[$attributes->getAttributeCode()]['attribute_options'][] = ['label'=> $multi_select_label, 'value_id'=> $multi_select_value];
	                    	}
	                }
					# code...
				} 
				/*else {
					if (!in_array($attributes->getAttributeCode(), $unused_attributes)) {
						$attribute_array[$attributes->getAttributeCode()]['attribute_code'] = $attributes->getAttributeCode();
						$attribute_array[$attributes->getAttributeCode()]['attribute_name'] = $attributes->getName();
						$attribute_array[$attributes->getAttributeCode()]['attribute_type'] = $attributes->getFrontendInput();
		                if ($attributes->getFrontendInput() == 'select') {
		                    foreach ($attributes->getOptions() as $option) {
		                    	if ($option->getLabel() != '' && strlen(trim($option->getLabel())) > 0 && $option->getLabel() != null)  {
		                    		$select_label = $option->getLabel();
		                    		$select_value = $option->getValue();

		                    		$attribute_array[$attributes->getAttributeCode()]['attribute_options'][] = ['label'=> ''.$select_label, 'value_id'=> ''.$select_value];
		                    	}
		                    }
		                }
		                if ($attributes->getFrontendInput() == 'multiselect') {
		                	if ($option->getLabel() != '' && strlen(trim($option->getLabel())) > 0 && $option->getLabel() != null)  {
		                    		$multi_select_label = $option->getLabel();
		                    		$multi_select_value = $option->getValue();
		                    		
		                    		$attribute_array[$attributes->getAttributeCode()]['attribute_options'][] = ['label'=> ''.$multi_select_label, 'value_id'=> ''.$multi_select_value];
		                    	}
		                }
					}
				}
*/
	        }
	        return $attribute_array;
		} else {
			throw new Exception(__('Please regenerate your authentication token. Your current authentication token has been expired.'));
		}
	}
}