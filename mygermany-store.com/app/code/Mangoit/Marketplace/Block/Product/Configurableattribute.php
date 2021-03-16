<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Block\Product;

/**
 * Mangoit Marketplace Product Configurableattribute Block.
 */
class Configurableattribute extends \Webkul\Marketplace\Block\Product\Configurableattribute
{

    protected $_attributeModel;
    protected $_session;
    /**
     * @param Magento\Framework\View\Element\Template\Context $context
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mangoit\VendorAttribute\Model\Attributemodel $attributeModel,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_attributeModel = $attributeModel;
        $this->_session = $session;
        parent::__construct($context, $data);
    }

    

    public function getVendorAttributesCollection()
    {
        //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $customerId = $customerSession->getCustomer()->getId();
        $collection = $this->_attributeModel->getCollection()->addFieldToFilter('vendor_id', ['eq'=> $customerId]);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    public function getAttributeLabel($id)
    {
        $model = $this->_attributeModel->load($id, 'attribute_id');
        if (!$model->isEmpty()) {
            return $model->getAttributeLabel();
        } else {
            return false;
        }
    }


    public function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getVendorAttributesCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mangoit.vendorattribute.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)
            ->setCollection($this->getVendorAttributesCollection());
            $this->setChild('pager', $pager);
            $this->getVendorAttributesCollection()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
