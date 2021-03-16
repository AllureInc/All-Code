<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Vendorcommission
 * @author    Mangoit
 */
namespace Mangoit\Vendorcommission\Controller\Adminhtml\Commission;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Resetcommission extends Action
{
    protected $_objectManager;
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mangoit_Marketplace::seller');
    }

    public function execute()
    {
        $messageManager = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        $parameters = $this->getRequest()->getParams();
        $customerId = $parameters['customer_id'];
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
        if (!empty($model->load($customerId, 'seller_id')->getData() ) ) {
            $model->load($customerId, 'seller_id');
            $model->setCommissionRule();
            $model->save();
        } else {
            echo "noseller";
            exit();
        }
        // $messageManager = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        $messageManager->addSuccessMessage('Commission setting has been reset.');
        echo "true"; 

    }
}
