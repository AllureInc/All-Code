<?php
namespace Ced\Twiliosmsnotification\Controller\Test;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
 

class Index extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    protected $_objectManager;

    public function __construct(
                            Context $context, 
                            PageFactory $pageFactory, 
                            \Magento\Framework\ObjectManagerInterface $objectManager
                            ) 
    {
        $this->pageFactory = $pageFactory;
        $this->_objectManager = $objectManager;
        return parent::__construct($context);
    }
 
    public function execute() {
    	$page_object = $this->pageFactory->create();
    	$page_object->getConfig()->getTitle()->prepend(__('Hello World'));

        var_dump($this->_objectManager->create('\Ced\Twiliosmsnotification\Helper\Data')->isSectionEnabled('customer_registration/enabled'));

        
        //return $page_object;
    }    
}
?>