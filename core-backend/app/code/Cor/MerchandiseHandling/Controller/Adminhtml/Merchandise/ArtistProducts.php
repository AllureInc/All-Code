<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Ajax Controller
 * Fetch products associated with artists on 'onchange' event of Artists Dropdown.
 */
namespace Cor\MerchandiseHandling\Controller\Adminhtml\Merchandise;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class ArtistProducts extends Action
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
        $resultPage = $this->resultPageFactory->create();
 
        $block = $resultPage->getLayout()
                ->createBlock('Cor\MerchandiseHandling\Block\Adminhtml\ArtistProducts')
                ->setTemplate('Cor_MerchandiseHandling::artist_products.phtml')
                ->toHtml();
        echo json_encode(array('data' => $block));
        exit;
    }
}
