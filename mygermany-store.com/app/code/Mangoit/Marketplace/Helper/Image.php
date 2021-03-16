<?php
/**
 * Copyright © Mangoit, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Catalog image helper
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Image extends \Magento\Catalog\Helper\Image
{
    /**
     * Media config node
     */
    const MEDIA_TYPE_CONFIG_NODE = 'images';

    /**
     * Current model
     *
     * @var \Magento\Catalog\Model\Product\Image
     */
    protected $_model;

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $_scheduleResize = true;

    /**
     * Scheduled for rotate image
     *
     * @var bool
     */
    protected $_scheduleRotate = false;

    /**
     * Angle
     *
     * @var int
     */
    protected $_angle;

    /**
     * Watermark file name
     *
     * @var string
     */
    protected $_watermark;

    /**
     * Watermark Position
     *
     * @var string
     */
    protected $_watermarkPosition;

    /**
     * Watermark Size
     *
     * @var string
     */
    protected $_watermarkSize;

    /**
     * Watermark Image opacity
     *
     * @var int
     */
    protected $_watermarkImageOpacity;

    /**
     * Current Product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Product image factory
     *
     * @var \Magento\Catalog\Model\Product\ImageFactory
     */
    protected $_productImageFactory;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

     
    protected $configView;

    /**
     * Image configuration attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var \Magento\Catalog\Model\View\Asset\PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;

    private $helper;

    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\ImageFactory $productImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholderFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\ImageFactory $productImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholderFactory = null,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $productImageFactory, $assetRepo, $viewConfig, $placeholderFactory);
    }


    /**
     * Set watermark properties
     *
     * @return $this
     */
    protected function setWatermarkProperties()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');//get current product
        $flag = false;
        if (!empty($product)) {
            $helperData = $this->helper->getSellerProductDataByProductId($product->getId());
            $helperData = $this->helper->getSellerProductDataByProductId($product->getId())->getFirstItem();
            $sellerData = $this->helper->getSellerDataBySellerId($helperData->getSellerId())->getFirstItem();
            if (!empty($sellerData)) {
                $storeId = $this->_storeManager->getStore()->getId();
                $mediaUrl = 'stores/'.$storeId.'/'.$sellerData->getProductWatermarkImage();
                $flag = true;
            }
        }
        if ($flag && (!empty($sellerData->getProductWatermarkImage()))) {
            $this->setWatermark($mediaUrl);
        } else {
            $this->setWatermark(
                $this->scopeConfig->getValue(
                    "design/watermark/{$this->getType()}_image",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );   
        }

        if (isset($sellerData) && !empty($sellerData->getWatermarkOpacity())) {
            $this->setWatermarkImageOpacity($sellerData->getWatermarkOpacity());
        } else {
            $this->setWatermarkImageOpacity(
                $this->scopeConfig->getValue(
                    "design/watermark/{$this->getType()}_imageOpacity",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        if (isset($sellerData) && !empty($sellerData->getWatermarkImagePosition())) {
            $this->setWatermarkPosition($sellerData->getWatermarkImagePosition());
        } else {
            $this->setWatermarkPosition(
                $this->scopeConfig->getValue(
                    "design/watermark/{$this->getType()}_position",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        if (isset($sellerData) && !empty($sellerData->getWatermarkImageSize())) {
            $this->setWatermarkSize($sellerData->getWatermarkImageSize());
        } else {
            $this->setWatermarkSize(
                $this->scopeConfig->getValue(
                    "design/watermark/{$this->getType()}_size",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        return $this;
    }
}
