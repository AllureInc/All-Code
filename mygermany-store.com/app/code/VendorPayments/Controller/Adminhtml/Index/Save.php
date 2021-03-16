<?php

namespace Mangoit\VendorPayments\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\VendorPayments\Model\Paymentfees;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action
{
    /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $_resultPageFactory;

    /**
      * @var \Mangoit\VendorPayments\Model\Paymentfees
      */
    protected $_paymentFeesModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Paymentfees $paymentFeesModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Paymentfees $paymentFeesModel
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_paymentFeesModel = $paymentFeesModel;
    }

    public function execute()
    {
    	$postData = $this->getRequest()->getParams();
    	$collection = $this->_paymentFeesModel->getCollection();
    	$returnArray = [];
    	try{
	    	foreach ($postData as $paymentTyp => $paymentFeeData) {
	    		if ($paymentTyp == 'paypal' || $paymentTyp == 'credit_card'){
	    			foreach ($paymentFeeData as $origin => $data) {
	    				$originKey = ($paymentTyp == 'paypal') ? 'counrty_group' : 'card_type';
						$effCountries = isset($data['effective_countries']) ? $data['effective_countries'] : array();

	    				$collection->addFieldToFilter('payment_method', $paymentTyp);
	    				$collection->addFieldToFilter($originKey, $origin);

						$saveData = [];
						$saveData['payment_method'] = $paymentTyp;
						$saveData['cost_per_tans'] = $data['cost_per_t']['fixed'];
						$saveData['counrty_group'] = ($paymentTyp == 'paypal') ? $origin : '';
						$saveData['card_type'] = ($paymentTyp == 'credit_card') ? $origin : '';
						$saveData['percent_of_total_per_tans'] = $data['cost_per_t']['in_percent'];
						$saveData['effective_countries'] = ($paymentTyp == 'paypal') ? implode(',',$effCountries) : '';
	    				if($collection->count()) {
	    					$model = $collection->getFirstItem();
	    					$id = $model->getId();
	    					$model->setData($saveData)->setId($id)->save();
	    				} else {
	    					$this->_paymentFeesModel->setData($saveData)->save();
	    				}
	    				$collection->clear()->getSelect()->reset('where');
	    			}
	    		} elseif ($paymentTyp == 'crypto' || $paymentTyp == 'other') {
	    			$collection->addFieldToFilter('payment_method', $paymentTyp);

					$saveData = [];
					$saveData['payment_method'] = $paymentTyp;
					$saveData['percent_of_total_per_tans'] = $paymentFeeData['cost_per_t']['in_percent'];

					if($collection->count()) {
						$model = $collection->getFirstItem();
	    				$id = $model->getId();
	    				$model->setData($saveData)->setId($id)->save();
					} else {
						$this->_paymentFeesModel->setData($saveData)->save();
					}
					$collection->clear()->getSelect()->reset('where');
	    		}
	    	}
	    	$returnArray['success'] = true;
            $this->messageManager->addSuccess(__('Payment Fees has been saved succesfully.'));
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
