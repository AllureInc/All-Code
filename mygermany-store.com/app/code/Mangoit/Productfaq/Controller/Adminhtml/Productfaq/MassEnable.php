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
 * Class MassEnable
 */
class MassEnable extends \Ced\Productfaq\Controller\Adminhtml\Productfaq\MassEnable
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
        foreach ($collection as $item) {
            if ($item->getVendorId() == 0) {
                $item->setIsActive(true);
                $item->save();
                continue;
            }
            //check either customer translated any FAQ or not - Start
            $sellerCustomerRecord = $_customerRepositoryInterface->getById($item->getVendorId());
            $isTranslated = 0;
            if ($translationObj = $sellerCustomerRecord->getCustomAttribute('is_translated')) {
                $isTranslated = $translationObj->getValue();
            }
            //check either customer translated any FAQ or not - End
            if (!$isTranslated) {
                $misFaqObj = $_misProductFaq->load($item->getId(),'default_faq_id'); 
                $unsFaqIds = unserialize($misFaqObj->getStorewiseFaqIds());

                $modelNewObj = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                $faqsToEnable = $modelNewObj->getCollection()->addFieldToFilter('id',['in' => $unsFaqIds]);
                foreach ($faqsToEnable as $faqEnableVlue) {
                    $faqEnableVlue->setIsActive(true);
                    $faqEnableVlue->save();
                }
                $misFaqObj->setIsActive(true);
                $misFaqObj->save();

            } else {
                $item->setIsActive(true);
                $item->save();
            }

        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collection->getSize()));

        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
*/
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
