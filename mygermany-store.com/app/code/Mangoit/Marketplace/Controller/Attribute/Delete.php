<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */


namespace Mangoit\Marketplace\Controller\Attribute;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace Product Attribute Save controller.
 */
class Delete extends \Magento\Customer\Controller\AbstractAccount
{   
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    protected $_attribute;
    protected $resultJsonFactory;
    protected $_vendorAttrModel;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Mangoit\VendorAttribute\Model\Attributemodel $vendorAttrModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
 
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_attribute = $attribute;
        $this->_vendorAttrModel = $vendorAttrModel;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
 
    } 
    /**
     * loads custom layout
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attributeid');
        $result = $this->resultJsonFactory->create();
        if ($id) {
            // entity type check
            $attributeObj = $this->_attribute->load($id);
            $vendorAttrObj = $this->_vendorAttrModel->load($id,'attribute_id');
            if ($attributeObj->getEntityTypeId() != 4) {
                $message = __('We can\'t delete the attribute.');
                return $result->setData(['Error' => true,'message' => $message]);
                exit;
            }
            try {

                $attributeObj->delete();
                $vendorAttrObj->delete();
                $message = __('You deleted the product attribute.');
                return $result->setData(['Success' => true,'message' => $message]);
            } catch (\Exception $e) {
                return $result->setData(['error' => true,'message' => $e->getMessage()]);
                $this->getResponse()->setBody($e->getMessage());
            }
        }
        exit();
    }
}
