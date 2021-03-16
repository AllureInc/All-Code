<?php 

namespace Mangoit\Vendorcommission\Controller\Adminhtml\Globalcommission;
/**
* 
*/
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\ResultFactory;

class Save extends Action
{
	protected $_objectmanager;
	protected $_turnoverFactory;
	protected $_messageManager;
	public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Mangoit\Vendorcommission\Model\TurnoverFactory $turnoverFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectmanager = $objectmanager;
        $this->_turnoverFactory = $turnoverFactory;
        $this->_messageManager = $messageManager;
    }

    public function execute()
    {
    	$rule = [];
    	$finalArray = array();
    	$originalRequestData = $this->getRequest()->getPostValue();
    	foreach ($originalRequestData as $key => $value) {
    		if ($key== 'commission') {
    			foreach ($value as $newkey => $newvalue) {
    			    $rule[$newkey] = $newvalue;
    			}
    		}
    	}
        foreach ($rule as $key => $value) {
        	$explodedComm = explode(',', $key);
        	$finalArray[$explodedComm[0]][$explodedComm[1]] = $value;
        }
        $serializeArray = serialize($finalArray);
        $collection = $this->_turnoverFactory->create();
        $collection->load(1);
        $collection->setCommissionRule($serializeArray);
        $flag = $collection->save();
        if (isset($flag)) {
        	$this->_messageManager->addSuccessMessage('Global commission setting saved.');
        } else {
        	$this->_messageManager->addErrorMessage('Error');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
       return $resultRedirect;
    }
}