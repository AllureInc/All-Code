<?php
namespace Mangoit\Productfaq\Controller\Adminhtml\Product;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Ced\Productfaq\Model\ResourceModel\Productfaq\CollectionFactory;

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $date;
    protected $seller;
    protected $_customerRepositoryInterface;
    protected $_misProductFaq;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\Seller $seller,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq,
        array $data = []
    ) {
        $this->date = $date;
        $this->seller = $seller;
        $this->collectionFactory = $collectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_misProductFaq = $misProductFaq;
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
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ids = $this->getRequest()->getParam('faq_mass_delete');
        $deleteIds = $this->getRequest()->getParams('deleteIds');

        $storeId = $this->getRequest()->getParam('id');


        if (!empty($ids)) {
            $checkRedirection = true;
        } else if(!isset($deleteIds['faq_mass_delete'])) {
            $ids = $deleteIds;
            $checkRedirection = false;
        } else {
            $this->messageManager->addError(__('Please select FAQs to delete.'));
            return $resultPage->setPath(
                '*/*/faq',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        //Admin faqs delete
       
        $faqCollection = $this->collectionFactory->create()->addFieldToFilter('id',$ids);
        foreach ($faqCollection as $value) {
            $isTranslated = 0;
            if ($value->getVendorId() != 0) {
                $sellerCustomerRecord = $this->_customerRepositoryInterface->getById($value->getVendorId());
                // check if customer translated or not
                if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
                    $isTranslated = $translationObj->getValue();
                }
                // if storeId is 0 i.e. all store view then delete all FAQs, othewise storewise delete FAQs
                if (!$storeId) {
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
                    $misFaqObj->delete();
                } else{
                    $value->delete();
                }
                // if (!$isTranslated) {
                //     if ($value->getParentFaqId() == 0) {
                //         $misFaqObj = $this->_misProductFaq->load($value->getId(),'default_faq_id');
                //     } else {
                //         $misFaqObj = $this->_misProductFaq->load($value->getParentFaqId(),'default_faq_id');
                //     }
                //     $unserilizedFaqs = unserialize($misFaqObj->getStorewiseFaqIds());
                //     foreach ($unserilizedFaqs as $unsValue) {
                //         $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                //         $editObj = $model->load($unsValue);
                //         $editObj->delete();
                //     }
                // } else {
                //     $value->delete();
                // }
            } else {
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