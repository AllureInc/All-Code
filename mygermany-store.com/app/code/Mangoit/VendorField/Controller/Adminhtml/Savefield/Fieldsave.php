<?php
namespace Mangoit\VendorField\Controller\Adminhtml\Savefield;
/**
* 
*/
use Magento\Framework\App\Action\Action;

class Fieldsave extends Action
{
	protected $_objectManager;
	protected $_session;
	public function __construct(\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magento\Framework\ObjectManagerInterface $objectmanager
		)
	{
		$this->_objectManager = $objectmanager;
		$this->_session = $coreSession;
		parent::__construct($context);
	}

	public function execute()
	{
		$parameters = $this->getRequest()->getParams();
		$flag = 0;
		$this->_session->start();
		$this->_session->unsFieldValue();		
		if (isset($parameters['custom_field_value'])) {
			$this->_session->setFieldValue($parameters['custom_field_value']);
			$flag = 1;
		} else {
			$this->_session->setFieldValue('none');
		}

		echo "true";
	}
}