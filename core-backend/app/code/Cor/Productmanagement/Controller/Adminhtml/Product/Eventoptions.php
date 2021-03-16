<?php
/**
 * Module: Cor_Productmanagement
 * Backend Ajax Controller
 * Fetch events associated with artists on 'onchange' event of the artists dropdown.
 */
namespace Cor\Productmanagement\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Eventoptions extends Action
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
    public function __construct( Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create(); 
        $block = $resultPage->getLayout()
            ->createBlock('Cor\Productmanagement\Block\Adminhtml\Artist\Eventoptions')
            ->setTemplate('Cor_Productmanagement::artist/event_options.phtml')
            ->toHtml();
        echo json_encode(array('data' => $block));
        exit;
    }
}
