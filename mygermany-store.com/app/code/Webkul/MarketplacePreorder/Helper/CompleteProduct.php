<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Area;

class CompleteProduct extends \Webkul\MarketplacePreorder\Helper\Data
{
    /**
     * Create Preorder Complete Product if Not Exists.
     */
    public function createPreOrderProduct()
    {
        $preorderProductId = $this->getPreorderCompleteProductId();
        $attributeSetId = $this->_productFactory->create()->getDefaultAttributeSetId();
        if ($preorderProductId == 0 || $preorderProductId == '') {
            try {
                $websiteIds = $this->getWebsiteIds();
                $stockData = [
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 0,
                                'is_in_stock' => 1,
                                'qty' => 999999999,
                            ];
                $preorderProduct = $this->_productFactory->create();
                $preorderProduct->setSku('preorder_complete');
                $preorderProduct->setName('Complete PreOrder');
                $preorderProduct->setAttributeSetId($attributeSetId);
                $preorderProduct->setCategoryIds([2]);
                $preorderProduct->setWebsiteIds($websiteIds);
                $preorderProduct->setStatus(1);
                $preorderProduct->setVisibility(1);
                $preorderProduct->setTaxClassId(0);
                $preorderProduct->setTypeId('virtual');
                $preorderProduct->setPrice(0);
                $preorderProduct->setStockData($stockData);
                $preorderProduct->save();
                $this->addImage($preorderProduct);
                $this->setCustomOption($preorderProduct);
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }
    }

    /**
     * Get All Website Ids.
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        $websiteIds = [];
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }
        return $websiteIds;
    }

    /**
     * Add Image to Preorder Complete Product
     *
     * @param object $product
     */
    public function addImage($product)
    {
        $path = $this->getMediaPath().'mppreorder/images/preorder.png';
        if (file_exists($path)) {
            $types = ['image', 'small_image', 'thumbnail'];
            $product->addImageToMediaGallery($path, $types, false, false);
            $product->save();
        }
    }

        /**
     * Get Mediad Path.
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_filesystem
                    ->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath();
    }

    /**
     * Set Custom Option in Preorder Complete Product
     */
    public function setCustomOption($product)
    {
        $nameOption = [
                        'sort_order' => 1,
                        'title' => 'Product Name',
                        'price_type' => 'fixed',
                        'price' => '',
                        'type' => 'field',
                        'is_require' => 1,
                    ];
        $orderOption = [
                        'sort_order' => 2,
                        'title' => 'Order Refernce',
                        'price_type' => 'fixed',
                        'price' => '',
                        'type' => 'field',
                        'is_require' => 1,
                    ];

        $options = [$nameOption, $orderOption];
        foreach ($options as $arrayOption) {
            $this->createCustomOption($product, $arrayOption);
        }
    }

    /**
     * Create Custom Option
     *
     * @param object $product
     * @param array $data
     */
    public function createCustomOption($product, $data)
    {
        $product->setHasOptions(1);
        $product->getResource()->save($product);
        $option = $this->_option
                        ->create()
                        ->setProductId($product->getId())
                        ->setStoreId($product->getStoreId())
                        ->addData($data);
        $option->save();
        $product->addOption($option);
        $product->save();
    }
}
