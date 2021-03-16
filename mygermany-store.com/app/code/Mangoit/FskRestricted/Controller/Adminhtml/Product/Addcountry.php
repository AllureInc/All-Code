<?php

namespace Mangoit\FskRestricted\Controller\Adminhtml\Product;

use Mangoit\FskRestricted\Model\ResourceModel\Restrictedproduct\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Result\PageFactory;

/**
* 
*/
class Addcountry extends \Magento\Backend\App\Action
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
        CollectionFactory $collectionFactory
        )
	{
		$this->filter = $filter;
		$this->resultPageFactory = $resultPageFactory;
		$this->objectmanager = $objectmanager;
		$this->collectionFactory = $collectionFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		
		$resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Add Countries"));
        return $resultPage;
	}

	public function getPostData()
	{
		$collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        return $collection->getData();
	}

}