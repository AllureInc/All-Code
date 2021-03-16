<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Controller File
 */
namespace Cor\MerchandiseHandling\Controller\Adminhtml\Merchandise;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();  
		$this->resultPage->setActiveMenu('Cor_Artistcategory::cor_category_index');
		$this->resultPage ->getConfig()->getTitle()->prepend((__('Merchandise Handling')));
		return $this->resultPage;
    }
}
