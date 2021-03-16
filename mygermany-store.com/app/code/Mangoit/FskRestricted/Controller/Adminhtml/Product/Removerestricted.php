<?php

namespace Mangoit\FskRestricted\Controller\Adminhtml\Product;

use Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
* 
*/
class Removerestricted extends \Magento\Backend\App\Action
{
	protected $_date;
	protected $filter;
	protected $_formKeyValidator;
	protected $_customerSession;
	protected $_objectManager;
	protected $collectionFactory;

	public function __construct(

        Filter $filter,
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        CollectionFactory $collectionFactory
    )
	{

		$this->filter = $filter;
		$this->_objectManager = $objectmanager;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);		
	}

	public function execute()
	{
		$collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $productModel = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        foreach ($collection->getData() as $key => $value) {
        	$productModel->load($value['id']);
        	$productModel->delete();
        	$productModel->unsetData();	
        }

		$this->messageManager->addSuccess( __('You have removed restriction successfully. Please reindex Solr in order to apply your changes.'));
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
	}
}