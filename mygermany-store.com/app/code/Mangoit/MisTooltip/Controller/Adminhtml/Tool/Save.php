<?php
namespace Mangoit\MisTooltip\Controller\Adminhtml\Tool;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
* updated on 01_nov_18
*/
class Save extends Action
{
    protected $_objectManager;
    protected $_toolModel;
    protected $_store;
    protected $_messageManager;
    protected $_storeRepository;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\MisTooltip\Model\Tooltip $toolModel,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeRepository = $storeRepository;
        $this->_objectManager = $objectmanager;
        $this->_toolModel = $toolModel;
        $this->_store = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $msgCounter = 0;
        $stores = $this->_storeRepository->getList();
        $_messageManager = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        $_toolModel = $this->_objectManager->create('Mangoit\MisTooltip\Model\Tooltip');
        $parameters = $this->getRequest()->getParams();
        if (isset($parameters['store'])) 
        {
            if (isset($parameters['tooltip'])) {    
                
                foreach ($parameters['tooltip'] as $key => $value) {
                    $data = [
                            'section_id'=> $value['section_id'], 
                            'tooltip_id'=> $value['tooltip_id'], 
                            'tooltip_text'=> trim($value['tooltip_text'])
                        ];
                    if (isset($value['id'])) {
                        $_toolModel->load($value['id']);                       
                        $_toolModel->setSectionId($value['section_id']);
                        $_toolModel->setTooltipId($value['tooltip_id']);
                        $_toolModel->setTooltipText(trim($value['tooltip_text']));
                        $_toolModel->save();
                    } else {
                        // die('else 88');
                        $storesData = $this->checkToolTipExist($value['section_id'], $value['tooltip_id']);
                        if ((empty($storesData['new_stores'])) && (empty($storesData['existing_store']))) {
                            foreach ($stores as $store) {
                                $_toolModelManager = $this->_objectManager->create('Mangoit\MisTooltip\Model\Tooltip');
                                if ($store["store_id"] != 0) {
                                    $storeId = $store["store_id"];
                                    $data['store_id'] = $storeId;
                                    $_toolModelManager->clearInstance();
                                    $_toolModelManager->addData($data);
                                    $_toolModelManager->save();
                                    $_toolModelManager->clearInstance();
                                }
                            }
                        } elseif (!empty($storesData['new_stores'])) {
                            foreach ($storesData['new_stores'] as $key => $value) {
                                $_toolModelManager = $this->_objectManager->create('Mangoit\MisTooltip\Model\Tooltip');
                                $data['store_id'] = $value;
                                $_toolModelManager->clearInstance();
                                $_toolModelManager->addData($data);
                                $_toolModelManager->save();
                                $_toolModelManager->clearInstance();
                            } 

                        }
                        
                    }


                }
            }

            if ( (isset($parameters['removed_ids'])) && (!empty($parameters['removed_ids'])) ) {
                $removedIdsArray = explode(',', $parameters['removed_ids']);

                foreach ($removedIdsArray as $value) {
                    $_toolModel->load($value);
                    $_toolModel->delete();
                    $_toolModel->clearInstance();
                }
                $_messageManager->addSuccess(__('Your record(s) has been deleted successfully.'));                 
            }

            $_messageManager->addSuccess(__('Your record(s) has been saved successfully.'));   
            return $this->resultRedirectFactory->create()->setPath('*/*/', ['_secure' => $this->getRequest()->isSecure()]);   
        }
    }

    public function checkToolTipExist($section_id, $tooltip_id)
    {
        $storeRepository = $this->_objectManager->create('Magento\Store\Model\StoreRepository');
        $new_store_array = array();
        $saved_store_array = array();

        foreach ($storeRepository->getList() as $store) {
            
        $_toolModelManager = $this->_objectManager->create('Mangoit\MisTooltip\Model\Tooltip');
        $_toolModelManagerCollection = $_toolModelManager->getCollection();

            if ($store["store_id"] != 0) {
                $storeId = $store["store_id"];
                $filteredCollection = $_toolModelManagerCollection
                                    ->addFieldToFilter('section_id', ['eq'=> $section_id])
                                    ->addFieldToFilter('tooltip_id', ['eq'=> $tooltip_id])
                                    ->addFieldToFilter('store_id', ['eq'=> $storeId]);

                if (count($filteredCollection->getData()) > 0) {
                    $saved_store_array[] = $storeId;
                } else {
                    $new_store_array[] = $storeId;
                }

            }
        }

        return array('new_stores'=> $new_store_array, 'existing_store'=> $saved_store_array);

    }

    /*public function deletePreviousData($collection)
    {
        foreach ($collection as $items) {
            $this->_toolModel->load($items->getId())->delete();
            $this->_toolModel->clearInstance();
        }
    }*/
}