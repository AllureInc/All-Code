<?php
/**
 * Mangoit
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 */
namespace Mangoit\Productfaq\Controller\Adminhtml\Productfaq;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ced\Productfaq\Model\ResourceModel\Productfaq\CollectionFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Ced\Productfaq\Controller\Adminhtml\Productfaq\MassDelete
{

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
        $_misProductFaq = $this->_objectManager->create('Mangoit\Productfaq\Model\Misproductfaq');
        $_customerRepositoryInterface = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

        $collectionSize = $collection->getSize();
        // echo "<pre>";
        // print_r($collection->getData());

        foreach ($collection as $page) {
            //check either customer translated any FAQ or not - Start
            $customer_check = $this->_objectManager->get('Magento\Customer\Model\Customer');
            $customer_check->load($page->getVendorId());

            $isTranslated = 0;
            if ( $customer_check->getId() ) {
                if ($page->getVendorId() != 0) {
                    $sellerCustomerRecord = $_customerRepositoryInterface->getById($page->getVendorId());
                    if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
                        $isTranslated = $translationObj->getValue();
                    }
                } else {
                    $isTranslated = 1;
                }

                // the customer already exist
            } 

            //check either customer translated any FAQ or not - End
            // print_r($isTranslated);
            // die('died');
            $misFaqObj = $_misProductFaq->load($page->getId(),'default_faq_id'); 
            $unsFaqIds = unserialize($misFaqObj->getStorewiseFaqIds());
            if ($isTranslated == 0) {
                // die('died');
                $modelNewObj = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                $faqsToDelete = $modelNewObj->getCollection()->addFieldToFilter('id',['in' => $unsFaqIds]);
                foreach ($faqsToDelete as $faqDelVlue) {
                    $faqDelVlue->delete();
                }
                $misFaqObj->delete();
                $page->delete();

            } else {
                // print_r($page->getId());
                // die('now in else');
                $page->delete();
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
*/
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
