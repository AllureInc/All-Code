<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Helper;

use Magento\Catalog\Model\ProductFactory as CatalogProduct;
use Magento\Catalog\Model\Category as CatalogCategory;
use Magento\Downloadable\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Export extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * Catalog product
     *
     * @var CatalogProduct
     */
    protected $_productFactory;

    /**
     * Catalog Product Category
     *
     * @var CatalogCategory
     */
    protected $category;

    /**
     * @var Configurable
     */
    protected $_configurableProductType;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @var VariationMatrix
     */
    protected $_configurableProductVariationMatrix;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistryInterface;

    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_values;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\Marketplace\Helper\Data       $marketplaceHelper
     * @param \Webkul\MpMassUpload\Helper\Data      $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data   $jsonHelper
     * @param \Magento\Eav\Model\Config             $config
     * @param CatalogProduct                        $catalogProduct
     * @param CatalogCategory                       $category
     * @param Configurable                          $configurableProductType
     * @param ProductRepositoryInterface            $productRepositoryInterface
     * @param VariationMatrix                       $configurableProductVariationMatrix
     * @param StockRegistryInterface                $stockRegistryInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Eav\Model\Config $config,
        CatalogProduct $catalogProduct,
        CatalogCategory $category,
        Configurable $configurableProductType,
        ProductRepositoryInterface $productRepositoryInterface,
        VariationMatrix $configurableProductVariationMatrix,
        StockRegistryInterface $stockRegistryInterface
    ) {
        $this->_moduleManager = $context->getModuleManager();
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_config = $config;
        $this->_productFactory = $catalogProduct;
        $this->category = $category;
        $this->_configurableProductType = $configurableProductType;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_configurableProductVariationMatrix = $configurableProductVariationMatrix;
        $this->_stockRegistryInterface = $stockRegistryInterface;
        parent::__construct($context);
    }

    /**
     * Process Export Product Data
     *
     * @param string $productType
     * @param array $productIds
     *
     * @return array
     */
    public function exportProducts($productType, $productIds, $allowedAttributes) {
        $helper = $this->_massUploadHelper;
        $productsRow = [];
        $productsRow[0] = $this->prepareFileColumnRow($productType, $allowedAttributes);
        $productsDataRow = [];
        foreach ($productIds as $id) {
            /** @var $mageProduct \Magento\Catalog\Model\Product */
            $mageProduct = $this->_productFactory->create();
            $mageProduct->load($id);
            $productData = $mageProduct->getData();
            $mageProductType = $mageProduct->getTypeId();
            if ($mageProductType == $productType) {
                $wholeData = [];
                /*Get Category Names by category id (set by comma seperated)*/
                $categories = $this->getCategorysByIds($mageProduct['category_ids']);
                /*Get $taxClass Name by tax id*/
                $taxClass = $this->getAttributeOptionbyOptionId(
                    "tax_class_id",
                    trim($mageProduct['tax_class_id'])
                );
                $wholeData['category'] = $categories;
                $wholeData['name'] = $mageProduct['name'];
                $wholeData['description'] = $mageProduct['description'];
                $wholeData['short_description'] = $mageProduct['short_description'];
                $wholeData['sku'] = $mageProduct['sku'];
                $wholeData['price'] = $mageProduct['price'];
                $wholeData['special_price'] = $mageProduct['special_price'];
                $wholeData['special_from_date'] = $mageProduct['special_from_date'];
                $wholeData['special_to_date'] = $mageProduct['special_to_date'];
                $wholeData['tax_class_id'] = $taxClass;
                if (!empty($mageProduct['quantity_and_stock_status']['is_in_stock'])) {
                    $wholeData['is_in_stock'] = 'In Stock';
                } else {
                    $wholeData['is_in_stock'] = 'Out Of Stock';
                }
                if (!empty($mageProduct['quantity_and_stock_status']['qty'])) {
                    $wholeData['stock'] = $mageProduct['quantity_and_stock_status']['qty'];
                } else {
                    $wholeData['stock'] = 0;
                }
                /*Calculate product weight*/
                if ($productType != 'virtual' && $productType != 'downloadable') {
                    $wholeData['weight'] = $mageProduct['weight'];
                }
                $wholeData['images'] = $this->getImages($mageProduct);
                $wholeData['meta_title'] = $mageProduct['meta_title'];
                $wholeData['meta_keyword'] = $mageProduct['meta_keyword'];
                $wholeData['meta_description'] = $mageProduct['meta_description'];
                /*Set Downloadable Data*/
                if ($productType == 'downloadable') {
                    $wholeData = $this->getDownloadableData($mageProduct, $wholeData);
                }
                /*Set Configurable Data*/
                $associatedData = [];
                if ($productType == 'configurable') {
                    $result = $this->getConfigurableData($mageProduct,$wholeData);
                    $wholeData = $result['parent'];
                    $associatedData = $result['child'];
                }
                /*Set Custom Attributes Values*/
                if ($helper->canSaveCustomAttribute()) {
                    $wholeData = $this->getCustomAttributeData(
                        $mageProduct, $wholeData, $allowedAttributes
                    );
                }
                // /*Set Custom Options Values*/
                if ($helper->canSaveCustomOption()) {
                    $wholeData = $this->getCustomOptionData($mageProduct, $wholeData);
                }
                $productsDataRow[] = $wholeData;
                foreach ($associatedData as $value) {
                    $productsDataRow[] = $value;
                }
            }
        }
        $productsRow[1] = $productsDataRow;
        return $productsRow;
    }

    /**
     * Prepare File Column Row
     *
     * @param string $productType
     *
     * @return array
     */
    public function prepareFileColumnRow($productType, $allowedAttributes)
    {
        $helper = $this->_massUploadHelper;
        $wholeData[] = 'category';
        $wholeData[] = 'name';
        $wholeData[] = 'description';
        $wholeData[] = 'short_description';
        $wholeData[] = 'sku';
        $wholeData[] = 'price';
        $wholeData[] = 'special_price';
        $wholeData[] = 'special_from_date';
        $wholeData[] = 'special_to_date';
        $wholeData[] = 'tax_class_id';
        $wholeData[] = 'is_in_stock';
        $wholeData[] = 'stock';
        /*Calculate product weight*/
        if ($productType != 'virtual' && $productType != 'downloadable') {
            $wholeData[] = 'weight';
        }
        $wholeData[] = 'images';
        $wholeData[] = 'meta_title';
        $wholeData[] = 'meta_keyword';
        $wholeData[] = 'meta_description';
        /*Set Downloadable Data*/
        if ($productType == 'downloadable') {
            $wholeData[] = 'links_title';
            $wholeData[] = 'links_purchased_separately';
            $wholeData[] = 'downloadable_link_title';
            $wholeData[] = 'downloadable_link_price';
            $wholeData[] = 'downloadable_link_type';
            $wholeData[] = 'downloadable_link_file';
            $wholeData[] = 'downloadable_link_sample_type';
            $wholeData[] = 'downloadable_link_sample';
            $wholeData[] = 'downloadable_link_is_sharable';
            $wholeData[] = 'downloadable_link_is_unlimited';
            $wholeData[] = 'downloadable_link_number_of_downloads';
            $wholeData[] = 'samples_title';
            $wholeData[] = 'downloadable_sample_title';
            $wholeData[] = 'downloadable_sample_file';
            $wholeData[] = 'downloadable_sample_type';
        }

        /*Set Configurable Data*/
        if ($productType == 'configurable') {
            $wholeData[] = '_super_attribute_code';
            $wholeData[] = '_super_attribute_option';
        }
        /*Set Custom Attributes Values*/
        if ($helper->canSaveCustomAttribute()) {
            foreach ($allowedAttributes as $id => $code) {
                $code = trim($code);
                $wholeData[] = $code;
            }
        }
        // /*Set Custom Options Values*/
        if ($helper->canSaveCustomOption()) {
            $wholeData[] = 'custom_option';
        }
        return $wholeData;
    }

    /**
     * Get Category Ids From Name
     *
     * @param string $categories
     *
     * @return array
     */
    public function getCategorysByIds($categoryIds)
    {
        $categories = [];
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $category = $this->category->load($categoryId);
                if ($category->getId()) {
                    array_push($categories, $category->getName());
                }
            }
        }
        return implode(',', $categories);
    }

  	/**
  	 * getAttributeOptionIdbyOptionId This returns
  	 * @param string $attributeCode Conatines Attribute code
  	 * @param int $optionId Conatines Attribute Id
  	 * @var Object $attribute Eav Attribute model object
  	 * @var Int $optionId Containes Attribute option id corrosponding to option text
  	 * @return string
  	 */
  	public function getAttributeOptionbyOptionId($attributeCode, $optionId) {
        if ($optionId == "") {
            return $optionId;
        }
        $model = $this->_config;
        $attribute = $model->getAttribute('catalog_product', $attributeCode);
        if ($attribute) {
            if ($optionId == 0) {
                $optionText = 'None';
            } else {
                $optionText = $attribute->getSource()->getOptionText($optionId);
            }
            return $optionText;
        } else {
            return "";
        }
        return "";
  	}

    /**
     * Get product image data.
     *
     * @param \Magento\Catalog\Model\Product $mageProduct
     * @return string
     */
    public function getImages(\Magento\Catalog\Model\Product $mageProduct)
    {
        $mediaGalleryImages = $mageProduct->getMediaGalleryImages();
        $productImages = [];
        if (count($mediaGalleryImages) > 0) {
            foreach ($mediaGalleryImages as &$mediaGalleryImage) {
                $imageName = $mediaGalleryImage['file'];
                $mediaGalleryImageArr = explode('/', $imageName);
                $mediaGalleryImageCount = count($mediaGalleryImageArr);
                if (!empty($mediaGalleryImageArr[$mediaGalleryImageCount-1])) {
                   $imageName = $mediaGalleryImageArr[$mediaGalleryImageCount-1];
                }
                array_push($productImages, $imageName);
            }
        }

        return implode(',', $productImages);
    }

    /**
     * Get Product Downloadable Data.
     *
     * @param \Magento\Catalog\Model\Product $mageProduct
     * @return array
     */
    public function getDownloadableData(\Magento\Catalog\Model\Product $mageProduct, $wholeData)
    {
        if ($mageProduct->getTypeId() !== Type::TYPE_DOWNLOADABLE) {
            return $wholeData;
        }
        // Prepare downloadable link data
        $linkData = $mageProduct->getTypeInstance()->getLinks(
            $mageProduct
        );
        $wholeData['links_title'] = $mageProduct['links_title'];
        $wholeData['links_purchased_separately'] = $mageProduct['links_purchased_separately'];
        $downloadableLinkTitle = [];
        $downloadableLinkPrice = [];
        $downloadableLinkType = [];
        $downloadableLinkFile = [];
        $downloadableLinkSampleType = [];
        $downloadableLinkSample = [];
        $downloadableLinkIsSharable = [];
        $downloadableLinkIsUnlimited = [];
        $downloadableLinknumberOfDownloads = [];
        foreach ($linkData as $link) {
            array_push($downloadableLinkTitle, $link->getTitle());
            array_push($downloadableLinkPrice, $link->getPrice());
            array_push($downloadableLinkType, $link->getLinkType());
            if ($link->getLinkType() == 'file') {
                array_push($downloadableLinkFile, $link->getLinkFile());
            } else {
                array_push($downloadableLinkFile, $link->getLinkUrl());
            }
            array_push($downloadableLinkSampleType, $link->getSampleType());
            if ($link->getLinkType() == 'file') {
                array_push($downloadableLinkSample, $link->getSampleFile());
            } else {
                array_push($downloadableLinkSample, $link->getSampleUrl());
            }
            array_push($downloadableLinkIsSharable, $link->getIsShareable());
            array_push($downloadableLinkIsUnlimited, $link->getIsUnlimited());
            array_push($downloadableLinknumberOfDownloads, $link->getNumberOfDownloads());
        }
        $wholeData['downloadable_link_title'] = implode(',', $downloadableLinkTitle);
        $wholeData['downloadable_link_price'] = implode(',', $downloadableLinkPrice);
        $wholeData['downloadable_link_type'] = implode(',', $downloadableLinkType);
        $wholeData['downloadable_link_file'] = implode(',', $downloadableLinkFile);
        $wholeData['downloadable_link_sample_type'] = implode(',', $downloadableLinkSampleType);
        $wholeData['downloadable_link_sample'] = implode(',', $downloadableLinkSample);
        $wholeData['downloadable_link_is_sharable'] = implode(',', $downloadableLinkIsSharable);
        $wholeData['downloadable_link_is_unlimited'] = implode(',', $downloadableLinkIsUnlimited);
        $wholeData['downloadable_link_number_of_downloads'] = implode(',', $downloadableLinknumberOfDownloads);

        // Prepare downloadable sample data
        $sampleData = $mageProduct->getTypeInstance()->getSamples(
            $mageProduct
        );
        $wholeData['samples_title'] = $mageProduct['samples_title'];
        $downloadableSampleTitle = [];
        $downloadableSampleFile = [];
        $downloadableSampleType = [];
        foreach ($sampleData as $sample) {
            array_push($downloadableSampleTitle, $sample->getTitle());
            array_push($downloadableSampleType, $sample->getSampleType());
            if ($sample->getSampleType() == 'file') {
                array_push($downloadableSampleFile, $sample->getSampleFile());
            } else {
                array_push($downloadableSampleFile, $sample->getSampleUrl());
            }
        }
        $wholeData['downloadable_sample_title'] = implode(',', $downloadableSampleTitle);
        $wholeData['downloadable_sample_file'] = implode(',', $downloadableSampleFile);
        $wholeData['downloadable_sample_type'] = implode(',', $downloadableSampleType);
        return $wholeData;
    }

    /**
     * Get Product Configurable Data.
     *
     * @param \Magento\Catalog\Model\Product $mageProduct
     * @return array
     */
    public function getConfigurableData(\Magento\Catalog\Model\Product $mageProduct, $wholeData)
    {
        //$wholeData['_super_attribute_code'] = $mageProduct[];
        $superAttributeCode = [];
        $superAttributeOption = [];
        $confAttributes = (array) $this->_configurableProductType
        ->getConfigurableAttributesAsArray($mageProduct);
        $countAttr = count($confAttributes);
        foreach ($confAttributes as $confAttribute) {
            array_push($superAttributeCode, $confAttribute['label']);
            for ($i=1; $i < $countAttr ; $i++) {
                array_push($superAttributeOption, $confAttribute['values'][0]['label']);
            }
        }
        $wholeData['_super_attribute_code'] = implode(',', $superAttributeCode);
        $wholeData['_super_attribute_option'] = implode(',', $superAttributeOption);

        $configurableProductVariations = $this->getConfigurableProductVariationMatrix($confAttributes);
        $configurableProductMatrix = [];
        $confAttributes = [];
        $associatedProducts = [];
        if ($configurableProductVariations) {
            $usedProductAttributes = $this->_configurableProductType
            ->getUsedProductAttributes(
                $mageProduct
            );
            $productByUsedAttributes = $this->getConfigurableAssociatedProducts($mageProduct);
            foreach ($configurableProductVariations as $configurableProductVariation) {
                $confAttributeValues = [];
                foreach ($usedProductAttributes as $confAttribute) {
                    $confAttributeValues[$confAttribute->getAttributeCode()] =
                    $configurableProductVariation[
                        $confAttribute->getId()
                    ]['value'];
                }
                $key = implode('-', $confAttributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $productQty = $this->_stockRegistryInterface->getStockItem(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    )->getQty();

                    /*Get Category Names by category id (set by comma seperated)*/
                    $categories = $this->getCategorysByIds($product['category_ids']);
                    /*Get $taxClass Name by tax id*/
                    $taxClass = $this->getAttributeOptionbyOptionId(
                        "tax_class_id",
                        trim($product['tax_class_id'])
                    );
                    $associatedData['category'] = $categories;
                    $associatedData['name'] = $product['name'];
                    $associatedData['description'] = $product['description'];
                    $associatedData['short_description'] = $product['short_description'];
                    $associatedData['sku'] = $product['sku'];
                    $associatedData['price'] = $price;
                    $associatedData['special_price'] = $product['special_price'];
                    $associatedData['special_from_date'] = $product['special_from_date'];
                    $associatedData['special_to_date'] = $product['special_to_date'];
                    $associatedData['tax_class_id'] = $taxClass;
                    $associatedData['is_in_stock'] = $product['quantity_and_stock_status']['is_in_stock'];
                    $associatedData['stock'] = $productQty;
                    $associatedData['weight'] = $product['weight'];
                    $explodeKeyword = '/';
                    $associatedData['images'] = $this->getImages($product);
                    $associatedData['meta_title'] = $product['meta_title'];
                    $associatedData['meta_keyword'] = $product['meta_keyword'];
                    $associatedData['meta_description'] = $product['meta_description'];
                    $associatedData['_super_attribute_code'] = '';
                    $associatedData['_super_attribute_option'] = '';
                    $associatedProducts[] = $associatedData;
                }
            }
        }

        return ['parent' => $wholeData, 'child' => $associatedProducts];
    }

    /**
     * Retrieve all possible attribute values combinations.
     *
     * @return array
     */
    public function getConfigurableProductVariationMatrix($attribute)
    {
        return $this->_configurableProductVariationMatrix
        ->getVariations($attribute);
    }

    /**
     * get all list of Configurable associated products.
     *
     * @return array
     */
    protected function getConfigurableAssociatedProducts($mageProduct)
    {
        $usedProductAttributes = $this->_configurableProductType
        ->getUsedProductAttributes(
            $mageProduct
        );
        $productByUsedAttributes = [];
        foreach ($this->_getConfigurableAssociatedProducts($mageProduct) as $product) {
            $keys = [];
            foreach ($usedProductAttributes as $confAttribute) {
                /* @var $confAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $product->getData($confAttribute->getAttributeCode());
            }
            $productByUsedAttributes[implode('-', $keys)] = $product;
        }

        return $productByUsedAttributes;
    }

    /**
     * @return array
     */
    protected function _getConfigurableAssociatedProducts($product)
    {
        $associatedProductIds = $product->getAssociatedProductIds();
        if ($associatedProductIds === null) {
            return $this->_configurableProductType->getUsedProducts($product);
        }
        $products = [];
        foreach ($associatedProductIds as $associatedProductId) {
            try {
                $products[] = $this->_productRepositoryInterface->getById($associatedProductId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
        }

        return $products;
    }

    /**
     * Get Custom Attribute Data
     *
     * @param $mageProduct
     * @param $wholeData
     * @return Array
     */
    public function getCustomAttributeData($mageProduct, $wholeData, $allowedAttributes)
    {
        foreach ($allowedAttributes as $id => $code) {
            $code = trim($code);
            if (!empty($mageProduct[$code])) {
                if (is_array($mageProduct[$code])) {
                    $wholeData[$code] = $this->_jsonHelper->jsonEncode($mageProduct[$code]);
                } else {
                    $wholeData[$code] = $mageProduct[$code];
                }
            } else {
                $wholeData[$code] = '';
            }
        }
        return $wholeData;
    }

    /**
     * Get Custom Option Data
     *
     * @param $mageProduct
     * @param $wholeData
     * @return Array
     */
    public function getCustomOptionData($mageProduct, $wholeData)
    {
        $optionsArray = $mageProduct->getOptions();
        if ($optionsArray == null) {
            $optionsArray = [];
        }
        $values = [];
        foreach ($optionsArray as $option) {
            $optionValue = [];
            $optionValue['is_require'] = $option->getIsRequire();
            $optionValue['title'] = $option->getTitle();
            $optionValue['type'] = $option->getType();
            $optionValue['sort_order'] = $option->getSortOrder();
            $optionValue['sku'] = $option->getSku();
            $optionValue['values'] = [];
            if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                $i = 0;
                $itemCount = 0;
                foreach ($option->getValues() as $_value) {
                    /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
                    $optionValue['values'][$i] = [
                        'title' => $_value->getTitle(),
                        'price' => $_value->getPrice(),
                        'price_type' => $_value->getPriceType(),
                        'sku' => $_value->getSku(),
                        'sort_order' => $_value->getSortOrder(),
                    ];
                    $i++;
                }
            } else {
                $optionValue['price'] = $option->getPrice();
                $optionValue['price_type'] = $option->getPriceType();
            }
            $values[] = $optionValue;
        }
        $wholeData['custom_option'] = '';
        if (!empty($values)) {
            $wholeData['custom_option'] = $this->_jsonHelper->jsonEncode($values);
        }
        return $wholeData;
    }
}
