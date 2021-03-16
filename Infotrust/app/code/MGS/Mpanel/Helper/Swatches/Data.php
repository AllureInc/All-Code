<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mpanel\Helper\Swatches;

use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;
use Magento\Swatches\Model\Swatch;

/**
 * Contact base helper
 */
class Data extends \Magento\Swatches\Helper\Data
{
    /**
     * When we init media gallery empty image types contain this value.
     */
    const EMPTY_IMAGE_VALUE = 'no_selection';

    /**
     * Default store ID
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SwatchCollectionFactory
     */
    protected $swatchCollectionFactory;

    /**
     * Catalog Image Helper
     *
     * @var Image
     */
    protected $imageHelper;

    /**
     * Data key which should populated to Attribute entity from "additional_data" field
     *
     * @var array
     */
    protected $eavAttributeAdditionalDataKeys = [
        Swatch::SWATCH_INPUT_TYPE_KEY,
        'update_product_preview_image',
        'use_product_image_for_swatch'
    ];

    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SwatchCollectionFactory $swatchCollectionFactory,
        Image $imageHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->productCollectionFactory   = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Product $product
     * @param string $imageFile
     * @return array
     */
    /* protected function getAllSizeImages(ModelProduct $product, $imageFile)
    {
        $largeSize = $this->getImageSizeForDetails();
        $mediumSize = $this->getImageSize();
        $minSize = $this->getImageMinSize();
        return [
            'large' => $this->imageHelper->init($product, 'product_page_image_large')
                ->resize($largeSize['width'],$largeSize['height'])
                ->setImageFile($imageFile)
                ->getUrl(),
            'medium' => $this->imageHelper->init($product, 'product_page_image_medium')
                ->resize($mediumSize['width'],$mediumSize['height'])
                ->setImageFile($imageFile)
                ->getUrl(),
            'small' => $this->imageHelper->init($product, 'product_page_image_small')
                ->resize($minSize['width'],$minSize['height'])
                ->setImageFile($imageFile)
                ->getUrl(),
        ];
    } */

    /**
     * @param ModelProduct $product
     * @param string $imageFile
     * @return array
     */
    private function getAllSizeImages(ModelProduct $product, $imageFile)
    {
        $largeSize = $this->getImageSizeForDetails();
        $mediumSize = $this->getImageSize();
        $minSize = $this->getImageMinSize();

        return [
            'large' => $this->imageHelper->init($product, 'product_page_image_large')
                ->resize($largeSize['width'], $largeSize['height'])
                ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(true)
                ->setImageFile($imageFile)
                ->getUrl(),
            'medium' => $this->imageHelper->init($product, 'product_page_image_medium')
                ->resize($mediumSize['width'], $mediumSize['height'])
                ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(true)
                ->setImageFile($imageFile)
                ->getUrl(),
            'small' => $this->imageHelper->init($product, 'product_page_image_small')
                ->resize($minSize['width'], $minSize['height'])
                ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(true)
                ->setImageFile($imageFile)
                ->getUrl(),
        ];
    }

    /**
     * Method getting full media gallery for current Product
     *
     * Array structure: [
     *  ['image'] => 'http://url/pub/media/catalog/product/2/0/blabla.jpg',
     *  ['mediaGallery'] => [
     *      galleryImageId1 => simpleProductImage1.jpg,
     *      galleryImageId2 => simpleProductImage2.jpg,
     *      ...,
     *      ]
     * ]
     *
     * @param ModelProduct $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductMediaGallery(ModelProduct $product): array
    {
        if (!in_array($product->getData('image'), [null, self::EMPTY_IMAGE_VALUE], true)) {
            $baseImage = $product->getData('image');
        } else {
            $productMediaAttributes = array_filter($product->getMediaAttributeValues(), function ($value) {
                return $value !== self::EMPTY_IMAGE_VALUE && $value !== null;
            });
            foreach ($productMediaAttributes as $attributeCode => $value) {
                if ($attributeCode !== 'swatch_image') {
                    $baseImage = (string)$value;
                    break;
                }
            }
        }

        if (empty($baseImage)) {
            return [];
        }

        $resultGallery = $this->getAllSizeImages($product, $baseImage);

        $resultGallery['gallery'] = $this->getGalleryImages($product);

        return $resultGallery;
    }

    /**
     * @param ModelProduct $product
     * @return array
     */
    private function getGalleryImages(ModelProduct $product)
    {
        //TODO: remove after fix MAGETWO-48040
        $product = $this->productRepository->getById($product->getId());

        $result = [];
        $mediaGallery = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $media) {
            $result[$media->getData('value_id')] = $this->getAllSizeImages(
                $product,
                $media->getData('file')
            );
        }
        return $result;
    }

    public function getStoreConfig($node)
    {
        return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /* Get product image size */
    public function getImageSize()
    {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image_detail');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth)];
                break;
            // 1/2 Portrait
            case 2:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth*2)];
                break;
            // 2/3 Portrait
            case 3:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth * 1.5))];
                break;
            // 3/4 Portrait
            case 4:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3)];
                break;
            // 2/1 Landscape
            case 5:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth/2)];
                break;
            // 3/2 Landscape
            case 6:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3)];
                break;
            // 4/3 Landscape
            case 7:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4)];
                break;
        }

        return $result;
    }

    public function getImageSizeForDetails()
    {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image_detail');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth)];
                break;
            // 1/2 Portrait
            case 2:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth*2)];
                break;
            // 2/3 Portrait
            case 3:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth * 1.5))];
                break;
            // 3/4 Portrait
            case 4:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3)];
                break;
            // 2/1 Landscape
            case 5:
                $result = ['width' => round($maxWidth), 'height' => round($maxWidth/2)];
                break;
            // 3/2 Landscape
            case 6:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3)];
                break;
            // 4/3 Landscape
            case 7:
                $result = ['width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4)];
                break;
        }

        return $result;
    }

    public function getImageMinSize()
    {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = ['width' => 120, 'height' => 120];
                break;
            // 1/2 Portrait
            case 2:
                $result = ['width' => 120, 'height' => 240];
                break;
            // 2/3 Portrait
            case 3:
                $result = ['width' => 120, 'height' => 180];
                break;
            // 3/4 Portrait
            case 4:
                $result = ['width' => 120, 'height' => 160];
                break;
            // 2/1 Landscape
            case 5:
                $result = ['width' => 120, 'height' => 60];
                break;
            // 3/2 Landscape
            case 6:
                $result = ['width' => 120, 'height' => 80];
                break;
            // 4/3 Landscape
            case 7:
                $result = ['width' => 120, 'height' => 90];
                break;
        }

        return $result;
    }
}
