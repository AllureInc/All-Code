<?php 
namespace Mangoit\FskRestricted\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
* 
*/
class Saveallselectedproducts extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save
{
     /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var int
     */
    private $bulkSize;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory
     * @param \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param int $bulkSize
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Bulk\BulkManagementInterface $bulkManagement,
        \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory $operartionFactory,
        \Magento\Framework\DataObject\IdentityGeneratorInterface $identityService,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        int $bulkSize = 100
    ) {
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
        $this->bulkSize = $bulkSize;
        parent::__construct($context, $attributeHelper, $bulkManagement, $operartionFactory, $identityService, $serializer, $userContext, $bulkSize);
    }
	
	public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $inventoryData = $this->getRequest()->getParam('inventory', []);
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $itemIds = $this->attributeHelper->getProductIds();
        // echo "<pre>";
        
        // print_r(get_class_methods($this->attributeHelper));
        // print_r($this->attributeHelper->getProductIds());

        // print_r($attributesData);
        // die();
        if (isset($attributesData['restricted_product'])) {
        	foreach ($itemIds as $value) {
        		$this->setRestrictedToProduct($value, $attributesData['restricted_product']);
        	}
        }
        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);

        /* Prepare inventory data item options (use config settings) */
        $options = $this->_objectManager->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class)
            ->getConfigItemOptions();
        foreach ($options as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            $storeId = $this->attributeHelper->getSelectedStoreId();
            if ($attributesData) {
                $dateFormat = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
                    ->getDateFormat(\IntlDateFormatter::SHORT);

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_objectManager->get(\Magento\Eav\Model\Config::class)
                        ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput = new \Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                            $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                                ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
                            );
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    } elseif ($attribute->getFrontendInput() == 'multiselect') {
                        // Check if 'Change' checkbox has been checked by admin for this attribute
                        $isChanged = (bool)$this->getRequest()->getPost('toggle_' . $attributeCode);
                        if (!$isChanged) {
                            unset($attributesData[$attributeCode]);
                            continue;
                        }
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                }

                $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                    ->updateAttributes($this->attributeHelper->getProductIds(), $attributesData, $storeId);
            }

            if ($inventoryData) {
                // TODO why use ObjectManager?
                /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
                $stockRegistry = $this->_objectManager
                    ->create(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
                /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
                $stockItemRepository = $this->_objectManager
                    ->create(\Magento\CatalogInventory\Api\StockItemRepositoryInterface::class);
                foreach ($this->attributeHelper->getProductIds() as $productId) {
                    $stockItemDo = $stockRegistry->getStockItem(
                        $productId,
                        $this->attributeHelper->getStoreWebsiteId($storeId)
                    );
                    if (!$stockItemDo->getProductId()) {
                        $inventoryData['product_id'] = $productId;
                    }

                    $stockItemId = $stockItemDo->getId();
                    $this->dataObjectHelper->populateWithArray(
                        $stockItemDo,
                        $inventoryData,
                        \Magento\CatalogInventory\Api\Data\StockItemInterface::class
                    );
                    $stockItemDo->setItemId($stockItemId);
                    $stockItemRepository->save($stockItemDo);
                }
                $this->_stockIndexerProcessor->reindexList($this->attributeHelper->getProductIds());
            }

            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel \Magento\Catalog\Model\Product\Action */
                $actionModel = $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class);
                $productIds = $this->attributeHelper->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                $this->_eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
            }

            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were updated.', count($this->attributeHelper->getProductIds()))
            );

            $this->_productFlatIndexerProcessor->reindexList($this->attributeHelper->getProductIds());

            if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
                || !empty($websiteRemoveData)
                || !empty($websiteAddData)
            ) {
                // $this->_productPriceIndexerProcessor->reindexList($this->attributeHelper->getProductIds());
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            // $this->messageManager->addException(
                // $e,
                // __('Something went wrong while updating the product(s) attributes.')
            // );
        }

        return $this->resultRedirectFactory->create()
            ->setPath('catalog/product/', ['store' => $this->attributeHelper->getSelectedStoreId()]);
    }

    public function setRestrictedToProduct($id, $attributeVal)
    {
        $saveModel = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
    	$productModel = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        $categoryArray = $this->getCategoriesId($id);
    	// $model = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct')->load($id, 'product_id');
        $model = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct')->getCollection();
        echo "<pre>";
        foreach ($categoryArray as $key => $value) {
            $model->clear()->getSelect()->reset('where');
            $model->addFieldToFilter('category_id', array('eq' => $value['code']))->addFieldToFilter('product_id', array('eq' => $id));
            print_r(count($model->getData()));
        
        

        	if(count($model->getData()) >= 1){
                // die("12121");
        		if ($attributeVal != 1) {
        			$model->delete();
        			$model->save();
        		}
        	} else {

        		if ($attributeVal == 1) {
        			$productName = $productModel->getName();
        			$status =  $productModel->getStatus();
        			if ($status != 1) {
    		        	$productStatus = 'Disabled';
    		        } else {
    		        	$productStatus = 'Enabled';
    		        }
        			$webkulProductModel = $this->_objectManager->create('Webkul\Marketplace\Model\Product');
    		        if ( !empty($webkulProductModel->load($id, 'mageproduct_id')->getData()) ) {
    		        	 $productCollection = $webkulProductModel->load($id, 'mageproduct_id');
    		        	$sellerID = $productCollection->getSellerId();
    		        	$sellerModel = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($sellerID, 'entity_id');
    		        	$vendorFirstName = $sellerModel->getFirstname();
    		        	$vendorLastName = $sellerModel->getLastname();
    		        	$vendorName = $vendorFirstName.' '.$vendorLastName;
    		        } else {
    		        	$vendorName = 'Admin';
    		        }

    		        $saveModel->setProductId($id);
                    $saveModel->setCategoryId($value['code']);
                    $saveModel->setCategoryName($value['name']);
    	        	$saveModel->setProductName($productName);
    	            $saveModel->setProductStatus($productStatus);
    	            $saveModel->setVendorName($vendorName);
    	            $saveModel->save();
                    $saveModel->unsetData();
        		}
        	}
        }
   
    }

    public function getCategoriesId($id)
    {
        $categoryArray = [];
        $categoryFactory = $this->_objectManager->get('Magento\Catalog\Model\CategoryFactory');
        $categoryHelper = $this->_objectManager->get('Magento\Catalog\Helper\Category');
        $categoryRepository = $this->_objectManager->get('Magento\Catalog\Model\CategoryRepository');
        $productModel = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        $catArray =  $productModel->getCategoryIds();
        foreach ($catArray as $value) {
            # code...
            $category = $categoryFactory->create()->load($value);
            array_push($categoryArray, array('code' => $value, 'name'=> $category->getName()));
        }
        // echo "<pre>";
        // print_r($categoryArray);
        // die();
        return $categoryArray;
    }
}