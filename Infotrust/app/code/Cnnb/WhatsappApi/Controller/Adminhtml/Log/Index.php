<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Controller For Mass action
 * For sending whatsapp notification
 */

namespace Cnnb\WhatsappApi\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;

class Index extends Action
{
    /**
     * @var $resultPageFactory
     */
    protected $_resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('WhatsApp Notification Logs')));
        return $resultPage;
    }
}
