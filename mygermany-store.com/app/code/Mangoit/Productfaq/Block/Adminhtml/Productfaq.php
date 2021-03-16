<?php

namespace Mangoit\Productfaq\Block\Adminhtml;

/**
* 
*/
class Productfaq extends \Magento\Backend\Block\Template
{
    protected $_objectManager;
    protected $registry;
    protected $productFaq;
    protected $_template = 'Mangoit_Productfaq::productfaqadmin.phtml';
    
    public function __construct(\Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Registry $registry,
        \Ced\Productfaq\Model\Productfaq $productFaq
    )
    {
        parent::__construct($context);
        $this->_objectManager = $objectmanager; 
        $this->registry = $registry;
        $this->productFaq = $productFaq;
    }

    protected function _construct()
    {
        $this->setTemplate($this->_template);
    }
    public function getFaqCollection()
    {
        $storeId = 0;
        $params = $this->getRequest()->getParams();
        if (isset($params['store'])) {
            $storeId = $params['store'];
        }
        $faqCollObj =$this->productFaq->getCollection();
        $faqCollObj->addFieldToFilter('product_id',$this->getCurrentProduct()->getId());
        // $faqCollObj->addFieldToFilter(['is_translated', 'vendor_id','store_id'],
        // [
        //     ['eq' => 1],
        //     ['eq' => 0],
        //     ['eq' => $storeId],
        // ]);
        $faqCollObj->addFieldToFilter(['vendor_id','store_id'],
        [
            ['eq' => 0],
            ['eq' => $storeId]
        ]);
        return $faqCollObj;
    }

    public function getCurrentProduct()
    {
        $product = $this->registry->registry('current_product');
        return $product;
    }

    public function getGetAllData($productId)
    {
        $allData = $this->_objectManager->create('Mangoit\VendorField\Helper\Data')->getModel($productId);
        return $allData;
    }

    public function getCustomFormKey()
    {
        $form = $this->_objectManager->create('Magento\Framework\Data\Form\FormKey');
        return $form->getFormKey();
    }
}