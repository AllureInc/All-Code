<?php
namespace Mangoit\Fskverified\Observer;
/**
* 
*/
use Magento\Framework\Event\ObserverInterface;

class FskVerifiedUserObserver implements ObserverInterface
{
	protected $_objectManager;
	protected $_observer;

	public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
	{
		$this->_objectManager = $objectManager;
        
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		die("observer called");
	}
}