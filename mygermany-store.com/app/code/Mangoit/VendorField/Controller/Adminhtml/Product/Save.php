<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\VendorField\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{
    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected $_objectManager;
    protected $authSession;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productCopier = $productCopier;
        $this->productTypeManager = $productTypeManager;
        $this->productRepository = $productRepository;
        $this->_objectManager = $objectmanager;
        $this->authSession = $authSession;
        parent::__construct($context, $productBuilder, $initializationHelper, $productCopier, $productTypeManager, $productRepository);
    }

    public function execute()
    {
        $error = 0;
        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->getStoreManager()->getStore($storeId);
        $this->getStoreManager()->setCurrentStore($store->getCode());
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $adminUser = $this->authSession->getUser();
        // --------------------- Working of custom field ----------------------

        $session = $this->_objectManager->create('Magento\Framework\Session\SessionManagerInterface');
        $session->start();
        $fieldData = $session->getFieldValue();
        
        if (($fieldData != 'none')) {
            if ( (! empty($fieldData)) ) {
                print_r($fieldData);
                foreach ($fieldData as $key => $value) {
                    if ( ($value['label_name'] == '') || (! (preg_match("/^[a-zA-Z0-9\s]+$/",$value['label_name']))) ) {
                        $error = 1;
                    } else if (($value['label_name'] == '0')) {
                        $error == 2; // if they do-not pass any value
                    }
                }
                if ($error == 0) {
                    $currentProductId = $data['product']['stock_data']['product_id'];
                    $currentProductName = $data['product']['name'];
                    echo "<br>currentProductId ".$currentProductId;
                    echo "<br>currentProductName ".$currentProductName;
                    $this->setCustomFieldData($currentProductId, $currentProductName, $fieldData); 
                    $session->unsetFieldValue();
                }
            }
        } else {
            $currentProductId = $data['product']['stock_data']['product_id'];
            $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
            $fieldModelSave = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
            $filteredData = $fieldModel->getCollection()->addFieldToFilter('product_id', array('eq'=> $currentProductId));
            if (count($filteredData->getData()) >= 1) { 
                foreach ($filteredData as $customField) {
                   $fieldModelSave->load($customField->getId());
                   $fieldModelSave->delete();
                   $fieldModelSave->unsetData();

                }
            }
        }
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        if ($data) {
            try {
                $product = $this->initializationHelper->initialize(
                    $this->productBuilder->build($this->getRequest())
                );
                $this->productTypeManager->processProduct($product);

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $this->handleImageRemoveError($data, $product->getId());
                $this->getCategoryLinkManagement()->assignProductToCategories(
                    $product->getSku(),
                    $product->getCategoryIds()
                );

                $productId = $product->getEntityId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();

                $this->copyToStores($data, $productId);

                if ($error == 1) {
                    $this->messageManager->addErrorMessage(__('Custom Fields are improper. '));
                    $session->unsFieldValue();
                }

                $this->messageManager->addSuccessMessage(__('You saved the product.'));
                $session->unsFieldValue();
                $this->getDataPersistor()->clear('catalog_product');
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNoticeMessage(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get(
                                \Magento\Framework\Escaper::class
                            )->escapeHtml($product->getName()),
                            $this->_objectManager->get(
                                \Magento\Framework\Escaper::class
                            )->escapeHtml($product->getSku())
                        )
                    );
                }
                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addExceptionMessage($e);
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            }
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
            $this->messageManager->addErrorMessage('No data to save');
            return $resultRedirect;
        }
        $dateObj = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $date = $dateObj->gmtDate();
        if ($session->getNewFaqs()) {
            $faqsArray = $session->getNewFaqs();
            foreach ($faqsArray as $faqVal) {
                $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                $model->setProductId($productId);
                $model->setTitle($faqVal['title']);
                $model->setDescription($faqVal['description']);
                $model->setEmailId($adminUser->getEmail());
                $model->setIsActive(1);
                $model->setUpdatedAt($date);
                $model->setPostedBy('myGermany Gmbh');
                $model->setPublishDate($date);
                // $model->setAdminNotification(1);
                $model->save(); 
            }
        }
        $session->unsNewFaqs();
        // update FAQs to approve state whenever product saves - start
        //$this->updateFAQs($productId);
        // update FAQs to approve state whenever product saves - end
        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getEntityId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }
        return $resultRedirect;
    }

    /**
     * Update all the FAQs of product to approve state.
     *
     * @param int $productId
     * @return void
     */
    private function updateFAQs($productId)
    {
        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
        $faqCollection = $model->getCollection('product_id',$productId);
        if ($faqCollection->count() > 0) {
            foreach ($faqCollection as $faqVal) {
                $faqVal->setIsActive(1);     
                $faqVal->save();     
            }
        }
    }

    /**
     * Notify customer when image was not deleted in specific case.
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNoticeMessage(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * Do copying data to stores
     *
     * @param array $data
     * @param int $productId
     * @return void
     */
    protected function copyToStores($data, $productId)
    {
        if (!empty($data['product']['copy_to_stores'])) {
            foreach ($data['product']['copy_to_stores'] as $websiteId => $group) {
                if (isset($data['product']['website_ids'][$websiteId])
                    && (bool)$data['product']['website_ids'][$websiteId]) {
                    foreach ($group as $store) {
                        $copyFrom = (isset($store['copy_from'])) ? $store['copy_from'] : 0;
                        $copyTo = (isset($store['copy_to'])) ? $store['copy_to'] : 0;
                        if ($copyTo) {
                            $this->_objectManager->create(\Magento\Catalog\Model\Product::class)
                                ->setStoreId($copyFrom)
                                ->load($productId)
                                ->setStoreId($copyTo)
                                ->setCopyFromView(true)
                                ->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
        }
        return $this->categoryLinkManagement;
    }

    /**
     * @return StoreManagerInterface
     * @deprecated 101.0.0
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }

    /**
     * Retrieve data persistor
     *
     * @return DataPersistorInterface|mixed
     * @deprecated 101.0.0
     */
    protected function getDataPersistor()
    {
        if (null === $this->dataPersistor) {
            $this->dataPersistor = $this->_objectManager->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    public function setCustomFieldData($currentProductId, $currentProductName, $fieldData)
    {
        
        $fieldArray = [];
        $postFieldsArray = [];
        $date = date("d-m-y h:i:s");
        /*foreach ($fieldData as $key => $value) {
            if ($fieldData['hasValue'] == 1) {
                array_push($postFieldsArray, $value['label_name']);
            }
        }*/
       
        $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        $idsNtoDelete = [];
        if (isset($fieldData)) {
            foreach ($fieldData as $customValue) {
                if (isset($customValue['current_id'])) {
                    $fieldModel->load($customValue['current_id']);
                    $fieldModel->setCustomFields($customValue['label_name']);
                    $fieldModel->setCustomFieldValue($customValue['label_value']);
                    $fieldModel->save();
                    $idsNtoDelete[] = $customValue['current_id'];
                    
                } else {
                    if (!empty($customValue['label_name'])) {
                        $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
                        $fieldModel->setProductId($currentProductId);
                        $fieldModel->setProductName($currentProductName);
                        $fieldModel->setCustomFields($customValue['label_name']);
                        $fieldModel->setCustomFieldValue($customValue['label_value']);
                        $fieldModel->save();
                        $idsNtoDelete[] = $fieldModel->getId();
                    }
                }
            }
            $filteredData = $fieldModel->getCollection()
            ->addFieldToFilter('product_id', array('eq'=> $currentProductId))
            ->addFieldToFilter('id', array('nin'=> $idsNtoDelete));
            if (!empty($filteredData)) {
                foreach ($filteredData as $delVal) {
                    $delVal->delete();
                }
            } 
        }
        
    }
}