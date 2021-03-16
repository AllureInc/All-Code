<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Plenty\Item\Helper\Rest\RouteInterface;
use Plenty\Core\Helper\Data as CoreHelper;

/**
 * Class Data
 * @package Plenty\Core\Helper
 */
class Data extends CoreHelper implements RouteInterface
{
    // GENERAL
    const STOCK_CONFIG_UPDATE_ON_ADDTOCART          = 'plenty_stock/general/update_on_addtocart';
    const STOCK_CONFIG_UPDATE_ON_SAVE               = 'plenty_stock/general/update_on_save';

    // DEVELOPER
    const CONFIG_DEV_LOG_DIRECTORY                  = 'log/plenty/';
    const CONFIG_DEV_DEBUG                          = 'plenty_item/dev/debug_enabled';
    const CONFIG_DEV_DEBUG_DIRECTORY_NAME           = 'plenty_item/dev/debug_directory';
    const CONFIG_DEV_DEBUG_LEVEL                    = 'plenty_item/dev/debug_level';

    /**
     * Returns Item Url
     *
     * @param null $itemId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getItemUrl($itemId = null)
    {
        if (null === $itemId) {
            return $this->getAppUrl(self::ITEM_URL);
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId);
    }

    /**
     * @param null $barcodeId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getItemBarcodeUrl($barcodeId = null)
    {
        if (null === $barcodeId) {
            return $this->getAppUrl(self::ITEM_BARCODE_URL);
        }
        return $this->getAppUrl(self::ITEM_BARCODE_URL.'/'.$barcodeId);
    }

    /**
     * @param null $priceId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getItemSalesPriceUrl($priceId = null)
    {
        if (null === $priceId) {
            return $this->getAppUrl(self::ITEM_SALES_PRICE_URL);
        }
        return $this->getAppUrl(self::ITEM_SALES_PRICE_URL. '/' .$priceId);
    }

    /**
     * Returns Variation Url
     *
     * @param null $itemId
     * @param null $variationId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationUrl($itemId = null, $variationId = null)
    {
        if (null === $itemId && null === $variationId) {
            return $this->getAppUrl(self::VARIATION_URL);
        }

        if (null === $variationId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId);
    }

    /**
     * @param null $itemId
     * @param null $variationId
     * @param null $categoryId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationCategoryUrl($itemId = null, $variationId = null, $categoryId = null)
    {
        if (null === $itemId && null === $variationId) {
            return $this->getAppUrl(self::VARIATION_CATEGORY_URL);
        }
        if (null === $categoryId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_categories');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_categories/'.$categoryId);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationDefaultCategoryUrl($itemId, $variationId)
    {
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_default_categories');
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $lang
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationDescriptionUrl($itemId, $variationId, $lang = null)
    {
        if (null === $lang) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/descriptions');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/descriptions/'.$lang);

    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $propertyId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationPropertyValueUrl($itemId, $variationId, $propertyId = null)
    {
        if (null === $propertyId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_properties');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $bundleId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationBundleUrl($itemId, $variationId, $bundleId = null)
    {
        if (null === $bundleId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_bundles');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_bundles/'.$bundleId);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $marketNumberId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationMarketNumbersUrl($itemId, $variationId, $marketNumberId = null)
    {
        if (null === $marketNumberId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/market_ident_numbers');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/market_ident_numbers/'.$marketNumberId);
    }

    /**
     * @param null $categoryId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getCategoryUrl($categoryId = null)
    {
        if (null === $categoryId) {
            return $this->getAppUrl(self::CATEGORY_URL);
        }
        return $this->getAppUrl(self::CATEGORY_URL.'/'.$categoryId);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getItemSalesPricesUrl()
    {
        return $this->getAppUrl(self::ITEM_SALES_PRICES_URL);
    }

    /**
     * @param $itemId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getItemImagesUrl($itemId)
    {
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/images');
    }

    /**
     * @param $itemId
     * @param null $imageId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getItemImageUploadUrl($itemId, $imageId = null)
    {
        if (null === $imageId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/images/upload');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/images/'.$imageId);
    }

    /**
     * @param null $attributeId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getAttributeUrl($attributeId = null)
    {
        if (null === $attributeId) {
            return $this->getAppUrl(self::ATTRIBUTE_URL);
        }
        return $this->getAppUrl(self::ATTRIBUTE_URL.'/'.$attributeId);
    }

    /**
     * @param $attributeId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getAttributeNamesUrl($attributeId)
    {
        return $this->getAppUrl(self::ATTRIBUTE_URL.'/'.$attributeId.'/names');
    }

    /**
     * @param $attributeId
     * @param null $valueId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getAttributeValueUrl($attributeId, $valueId = null)
    {
        if (null === $valueId) {
            return $this->getAppUrl(self::ATTRIBUTE_URL.'/'.$attributeId.'/values');
        }
        return $this->getAppUrl(self::ATTRIBUTE_URL.'/'.$attributeId.'/values/'.$valueId);
    }

    /**
     * @param $valueId
     * @param null $lang
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getAttributeValueNameUrl($valueId, $lang = null)
    {
        if (null === $lang) {
            return $this->getAppUrl(self::ATTRIBUTE_VALUES_URL.'/'.$valueId.'/names');
        }
        return $this->getAppUrl(self::ATTRIBUTE_VALUES_URL.'/'.$valueId.'/names/'.$lang);
    }

    /**
     * @param null $id
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getPropertiesUrl($id = null)
    {
        if (null === $id) {
            return $this->getAppUrl(self::PROPERTIES_URL);
        }
        return $this->getAppUrl(self::PROPERTIES_URL.'/'.$id);
    }

    /**
     * @param $propertyId
     * @param null $lang
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getPropertyNamesUrl($propertyId, $lang = null)
    {
        if (null === $lang) {
            return $this->getAppUrl(self::PROPERTIES_URL.'/'.$propertyId.'/names');
        }
        return $this->getAppUrl(self::PROPERTIES_URL.'/'.$propertyId.'/names/'.$lang);
    }

    /**
     * @param $propertyId
     * @param null $lang
     * @param null $selectionId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getPropertySelectionsUrl($propertyId, $selectionId = null, $lang = null)
    {
        if (null === $lang && null === $selectionId) {
            return $this->getAppUrl(self::PROPERTIES_URL.'/'.$propertyId.'/selections');
        }

        if (null !== $lang) {
            return $this->getAppUrl(self::PROPERTIES_URL.'/'.$propertyId.'/selections/'.$lang);
        }

        return $this->getAppUrl(self::PROPERTIES_URL.'/'.$propertyId.'/selections/'.$selectionId);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param $propertyId
     * @param null $lang
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getPropertyValueTextUrl($itemId, $variationId, $propertyId, $lang = null)
    {
        if (null === $lang) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId.'/texts');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId.'/texts/'.$lang);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationPropertiesUrl($itemId, $variationId)
    {
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_properties');
    }

    /**
     * @param $itemId
     * @param $variationId
     * @param null $priceId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationSalesPricesUrl($itemId, $variationId, $priceId = null)
    {
        if (null === $priceId) {
            return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_sales_prices');
        }
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/variation_sales_prices/'.$priceId);
    }

    /**
     * @param $itemId
     * @param $variationId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVariationStockCorrectionUrl($itemId, $variationId)
    {
        return $this->getAppUrl(self::ITEM_URL.'/'.$itemId.'/variations/'.$variationId.'/stock/correction');
    }

    /**
     * @param null $id
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getManufacturersUrl($id = null)
    {
        if (null === $id) {
            return $this->getAppUrl(self::MANUFACTURER_URL);
        }
        return $this->getAppUrl(self::MANUFACTURER_URL.'/'.$id);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isDebugOn()
    {
        return (bool) $this->_getConfig(self::CONFIG_DEV_DEBUG);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLogPath()
    {
        $dirName = $this->_getConfig(self::CONFIG_DEV_DEBUG_DIRECTORY_NAME) ?
            $this->_getConfig(self::CONFIG_DEV_DEBUG_DIRECTORY_NAME) : 'plenty-core';

        // return Mage::getBaseDir('var') . DS . self::CORE_CONFIG_DEV_LOG_DIRECTORY . $dirName;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDebugLevel()
    {
        return explode(',', $this->_getConfig(self::CONFIG_DEV_DEBUG_LEVEL));
    }
}
