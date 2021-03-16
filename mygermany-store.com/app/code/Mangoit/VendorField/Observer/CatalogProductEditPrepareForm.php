<?php
/**
 * @category    Webkul
 * @author      Webkul Software Pvt. Ltd.
 */
namespace Mangoit\VendorField\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class CatalogProductEditPrepareForm implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;
    protected $_objectManager;
 
    /**
     * @param \Magento\Framework\Registry  $coreRegistry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Registry  $coreRegistry
    ) {
        $this->_objectManager = $objectmanager;
        $this->_coreRegistry = $coreRegistry;
    }
 
    /**
     * product from prepare event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {  
        $form = $observer->getForm(); // $observer contain form object
 
        // get your group element in which you want to add custom filed
        // $fieldset = $form->getElement('group-fields-id-in-which-you-want-to-add-elements'); 
        $fieldset = $form->addFieldset('tiered_price', array('legend' => __('Tier Pricing'))); 
 
        if ($fieldset) {
 
            // get current product if you want to use any data from product
            $product = $this->_coreRegistry->registry('product');
            $fieldset ->addField(
                'custom-field-1',
                'text',
                [
                    'name' => 'custom-field-1',
                    'label' => __('Custom Field 1'),
                    'id' => 'custom-field-1'
                ]
            );
 
            $fieldset ->addField(
                'custom-field-2',
                'text',
                [
                    'name' => 'custom-field-2',
                    'label' => __('Custom Field 2'),
                    'id' => 'custom-field-2'
                ]
            );
 
            // You can set any data in these elements for display default value
            $form->addValues(
                [
                    'custom-field-1' => 'data according to you', // ex. $product->getName()
                    'custom-field-2' => 'data according to you'
                ]
            );
        }
        return $this;
    }
}