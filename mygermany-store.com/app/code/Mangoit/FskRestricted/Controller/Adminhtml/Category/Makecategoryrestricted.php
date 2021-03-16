<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorAttribute
 * @author    Mangoit
 */
namespace Mangoit\FskRestricted\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Makecategoryrestricted extends Action
{
    /**
     * @param Action\Context $context
     */
    protected $_productCollectionFactory;
    protected $_categoryCollectionFactory;
    protected $_categoryFactory;
    protected $_productRepository;
    protected $newObjectManager;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->newObjectManager = $objectmanager;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productRepository = $productRepository;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    // protected function _isAllowed()
    // {
    //     return $this->_authorization->isAllowed('Mangoit_VendorAttribute::vendorattribute');
    // }

    public function execute()
    {
        $mainProductCollection = $this->_productCollectionFactory->create();
        $mainProductCollection->addAttributeToSelect('entity_id');

        $param = $this->getRequest()->getParams();
        
        $countryNameArray = [];
        unset($param['key']);
        unset($param['form_key']);
        
        $countryId = $param['countryNames'];
        $categoryIds = json_decode($param['category_ids']);
        
        $countryModel = $this->newObjectManager->create('Magento\Directory\Model\Country');
        foreach ($countryId as $value) {
            $countryName = $countryModel->loadByCode($value)->getName();
            array_push($countryNameArray, $countryName);
        }
        $countries = implode(",", $countryNameArray);
    
        $restrictedProModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory');
    
        $categoryFactory = $this->newObjectManager->get('Magento\Catalog\Model\CategoryFactory');
        $magentoProductModel = $this->newObjectManager->get('Magento\Catalog\Model\Product');
        $categoryHelper = $this->newObjectManager->get('Magento\Catalog\Helper\Category');
        $categoryRepository = $this->newObjectManager->get('Magento\Catalog\Model\CategoryRepository');
    
        $productModelCollection = $restrictedProModel->getCollection();
        $categoryModelCollection = $categoryModel->getCollection();
    
        /**************************New Code*******************************/
        $categoryIds = (isset($categoryIds[0]) && is_array($categoryIds[0])) ? $categoryIds[0] : $categoryIds;
        $mageCatModel = $categoryFactory->create();
        foreach ($categoryIds as $catId) {

            $category = $mageCatModel->load($catId);
            $categoryName = $category->getName();

            $allChildCats = explode(',', $category->getAllChildren());

            $allChildCats[] = $catId;

            $mainProductCollection->addCategoriesFilter(['in' => $allChildCats]);

            foreach ($mainProductCollection->getData() as $product) {
                $loadedRstrctProd = $productModelCollection
                        ->addFieldToFilter('product_id', array('eq' => $product['entity_id']))
                        ->addFieldToFilter('category_id', array('eq' => $catId));

                $configProduct = $this->_productRepository->getById($product['entity_id']);
                $vendor = $this->getVendorName($product['entity_id']);

                if ($configProduct->getStatus() == '1') {
                    $status = 'Enabled';
                } else {
                    $status = 'Disabled';
                }

                if($loadedRstrctProd->count() > 0) {
                    $rstrctProdItem =  $restrictedProModel->load($loadedRstrctProd->getFirstItem()->getId());
                    $rstrctProdItem->setRestrictedCountries($countries);
                    $rstrctProdItem->save();
                    $rstrctProdItem->unsetData();
                } else {
                    $restrictedProModel->setCategoryId($catId);
                    $restrictedProModel->setCategoryName($categoryName);
                    $restrictedProModel->setProductId($product['entity_id']);
                    $restrictedProModel->setProductName($configProduct->getName());
                    $restrictedProModel->setProductStatus($status);
                    $restrictedProModel->setVendorName($vendor);
                    $restrictedProModel->setRestrictedCountries($countries);
                    $restrictedProModel->save();
                    $restrictedProModel->unsetData();
                }

                $productModelCollection->clear()->getSelect()->reset('where');
            }

            $categoryModelCollection->addFieldToFilter('category_id', array('eq' => $catId));

            if ($categoryModelCollection->count() > 0) {
                $loadedRstrctCat = $categoryModelCollection->getFirstItem();
                $loadedRstrctCat->setRestrictedCountries($countries);
                $loadedRstrctCat->save();
            } else {
                $categoryModel->setCategoryId($catId);
                $categoryModel->setCategoryName($categoryName);
                $categoryModel->setRestrictedCountries($countries);                  
                $categoryModel->save();
                $categoryModel->unsetData();
            }
            $categoryModelCollection->clear()->getSelect()->reset('where');
        }
        /**************************New Code*******************************/

        $this->messageManager->addSuccess( __('Categories restricted successfully.  Please reindex Solr in order to apply your changes.'));
        return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        
    }

    // public function allreadyExistData($categoryId)
    // {
    //     $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory');
    //     // echo "<br>".count($categoryModel->load($categoryId, 'category_id')->getData());
    //     if ( count($categoryModel->load($categoryId, 'category_id')->getData()) >= 1 ) {
    //         $categoryData = $categoryModel->load($categoryId, 'category_id');
    //         print_r(var_dump($categoryData->getRestrictedCountries()));
    //         if ($categoryData->getRestrictedCountries() != NULL) {
    //             return true;
    //         } else {
    //             return false;
    //         }
    //         // return true;
    //     } else {
    //         return false;
    //     }
    //     // return   $categoryModel->load($categoryId, 'category_id');
    // }

    // public function saveExistProductData($id, $countries)
    // {
    //     $productModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct')->load($id);
    //     $productModel->setRestrictedCountries($countries);
    //     $productModel->save();
    //     // $productModel->unsetData();

    // }

    // public function saveExistCategoryData($id, $countries)
    // {
    //     $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory')->load($id);
    //     $categoryModel->setRestrictedCountries($countries);
    //     $categoryModel->save();
    //     // $categoryModel->unsetData();
    // }

    // public function getAllParameters()
    // {
    //      $param = $this->getRequest()->getParams();
    //     unset($param['key']);
    //     unset($param['form_key']);
    //     unset($param['selectAll']);
    //     return $param;
    // }

    public function getVendorName($productId)
    {
        $webkulModel = $this->newObjectManager->create('Webkul\Marketplace\Model\Product');
        if ( !empty($webkulModel->load($productId, 'mageproduct_id')->getData()) ) {
                 $productCollection = $webkulModel->load($productId, 'mageproduct_id');
                $sellerID = $productCollection->getSellerId();
                $sellerModel = $this->newObjectManager->create('Magento\Customer\Model\Customer')->load($sellerID, 'entity_id');
                $vendorFirstName = $sellerModel->getFirstname();
                $vendorLastName = $sellerModel->getLastname();
                $vendorName = $vendorFirstName.' '.$vendorLastName;
        } else {
                $vendorName = 'Admin';
        }

        return $vendorName;
    }

}