<?php

namespace Mangoit\VendorAttribute\Controller\Adminhtml\Attribute;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\VendorAttribute\Model\ResourceModel\Attributemodel\CollectionFactory;
/**
* 
*/
class Makeglobal extends \Magento\Backend\App\Action
{
	protected $filter;
	protected $objectmanager;
	protected $resultPageFactory;
	protected $collectionFactory;

	public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory)
	{
		$this->filter = $filter;
		$this->collectionFactory = $collectionFactory;
		$this->resultPageFactory = $resultPageFactory;
		$this->objectmanager = $objectmanager;
		parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();
		$typeArray = [];
		$flag = 0;
        $resultPage->getConfig()->getTitle()->prepend(__("Make Global Attribute"));
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection->getData() as $key => $value) {
        	array_push($typeArray,$value['attribute_type']);
        }
        foreach ($collection->getData() as $key => $value) {
        	if(in_array($value['attribute_type'], $typeArray)){
        		if ($flag == 1) {
        			$flag = 0;
        		} else {
        			$flag = 1;
        		}
        	}
        }
        $typeSize = sizeof(array_count_values($typeArray));
        if ($typeSize > 1) {
        	$this->messageManager->addError(__('Please select the same type of attributes.'));
        	$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
        return $resultPage;
	}

}