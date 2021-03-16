<?php
namespace Mangoit\Productfaq\Controller\Product;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Ced\Productfaq\Model\ResourceModel\Productfaq\CollectionFactory;

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $date;
    protected $seller;
    protected $_customerRepositoryInterface;
    protected $_misProductFaq;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\Seller $seller,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->seller = $seller;
        $this->collectionFactory = $collectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_misProductFaq = $misProductFaq;
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (empty($this->getRequest()->getParams())) {
            $this->messageManager->addError(__('Please select FAQs to delete.'));
            return $resultPage->setPath(
                '*/*/faq',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        $ids = $this->getRequest()->getParam('faq_mass_delete');
        $deleteIds = $this->getRequest()->getParams('deleteIds');

        $customerId = $this->customerSession->getCustomer()->getId();
        $sellerCustomerRecord = $this->_customerRepositoryInterface->getById($customerId);
        // check if customer translated or not
        $isTranslated = 0;
        if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
            $isTranslated = $translationObj->getValue();
        }

        if (!empty($ids)) {
            $checkRedirection = true;
        } else if(!isset($deleteIds['faq_mass_delete'])) {
            $ids = $deleteIds;
            $checkRedirection = false;
        } else {
            $this->messageManager->addError(__('Please select FAQs to delete
                    .'));
            return $resultPage->setPath(
                '*/*/faq',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        if (!$isTranslated) {
            $faqCollection = $this->collectionFactory->create()->addFieldToFilter('id',$ids);
            foreach ($faqCollection as $value) {
                if ($value->getParentFaqId() == 0) {
                    $misFaqObj = $this->_misProductFaq->load($value->getId(),'default_faq_id');
                } else {
                    $misFaqObj = $this->_misProductFaq->load($value->getParentFaqId(),'default_faq_id');
                }
                $unserilizedFaqs = unserialize($misFaqObj->getStorewiseFaqIds());
                foreach ($unserilizedFaqs as $unsValue) {
                    $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                    $editObj = $model->load($unsValue);
                    $editObj->delete();
                }
            }
        } else {
            $faqCollection = $this->collectionFactory->create()->addFieldToFilter('id',$ids);
            foreach ($faqCollection as $value) {
                $value->delete();
            }
        }
        $this->messageManager->addSuccess(__('FAQs has been successfully deleted!.'));
        if ($checkRedirection) {
            return $resultPage->setPath(
                '*/*/faq',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            exit;
        }
    }
}