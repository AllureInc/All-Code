<?php
namespace Mangoit\Productfaq\Controller\Product;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $date;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('id');
        if (!empty($data)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            return $resultRedirect;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/faq');
        }
    }
     /** @var \Magento\Framework\View\Result\Page resultPage */
}