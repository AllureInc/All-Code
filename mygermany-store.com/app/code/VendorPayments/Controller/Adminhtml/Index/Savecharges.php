<?php

namespace Mangoit\VendorPayments\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\VendorPayments\Model\Exchangefees;
use Magento\Framework\Controller\ResultFactory;

class Savecharges extends Action
{
    /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $_resultPageFactory;

    /**
      * @var \Mangoit\VendorPayments\Model\Exchangefees
      */
    protected $_exchangeFeesModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Exchangefees $exchangeFeesModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Exchangefees $exchangeFeesModel
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_exchangeFeesModel = $exchangeFeesModel;
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();

        $collection = $this->_exchangeFeesModel->getCollection();
        $returnArray = [];
        try{
            $chargeData = (isset($postData['charge'])) ? $postData['charge'] : [];
            foreach ($chargeData as $chargeKey => $chargePecent) {

                $saveData = [];
                $saveData['base_to_target_currency'] = $chargeKey;
                $saveData['charge_percent'] = $chargePecent;

                $collection->addFieldToFilter('base_to_target_currency', $chargeKey);
                if($collection->count()) {
                    $model = $collection->getFirstItem();
                    $id = $model->getId();
                    $model->setData($saveData)->setId($id)->save();
                } else {
                    $this->_exchangeFeesModel->setData($saveData)->save();
                }
                $collection->clear()->getSelect()->reset('where');
            }
            $returnArray['success'] = true;
            $this->messageManager->addSuccess(__('Exchange charges have been saved succesfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $returnArray['success'] = false;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t pay the seller right now. %1'));
            $returnArray['success'] = false;
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($returnArray);
        return $resultJson;
    }
}
