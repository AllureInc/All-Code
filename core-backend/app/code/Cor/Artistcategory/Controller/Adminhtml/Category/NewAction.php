<?php
namespace Cor\Artistcategory\Controller\Adminhtml\Category;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class NewAction extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

     public function execute()
    {
        $this->_forward('edit');
    }
}
