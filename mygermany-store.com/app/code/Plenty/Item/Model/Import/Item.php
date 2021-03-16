<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\Model\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject\IdentityInterface;

use Plenty\Item\Model\Import\Item\AttributeValueFactory;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Rest\Item as ItemClient;
use Plenty\Item\Rest\Variation as VariationClient;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Core\Model\Profile\Config\Source\Behaviour;
use Plenty\Item\Model\ResourceModel\Import\Item\Property\CollectionFactory as ItemPropertyCollectionFactory;

/**
 * Class Item
 * @package Plenty\Item\Model\Import
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item getResource()
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Collection getCollection()
 *
 * @method \Plenty\Item\Profile\Import\Entity\Product getProfileEntity()
 * @method \Plenty\Item\Profile\Import\Entity\Product setProfileEntity(object $value)
 */
class Item extends ImportExportAbstract
    implements ItemInterface, IdentityInterface
{
    const CACHE_TAG = 'plenty_item_import_item';
    protected $_cacheTag = 'plenty_item_import_item';
    protected $_eventPrefix = 'plenty_item_import_item';

    /**
     * @var AttributeFactory
     */
    private $_attributeFactory;

    /**
     * @var Attribute
     */
    private $_attributeModel;

    /**
     * @var Item\VariationFactory
     */
    private $_variationFactory;

    /**
     * @var Item\Variation
     */
    private $_variationModel;

    /**
     * @var Item\Variation
     */
    private $_variation;

    /**
     * @var VariationClient
     */
    private $_variationApi;

    /**
     * @var ItemPropertyCollectionFactory
     */
    private $_propertyCollectionFactory;

    /**
     * @var Item\MediaFactory
     */
    private $_mediaFactory;

    /**
     * @var Item\Media
     */
    private $_mediaModel;

    /**
     * @var Item\ShippingProfileFactory
     */
    private $_shippingProfileFactory;

    /**
     * @var Item\ShippingProfile
     */
    private $_shippingProfileModel;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item::class);
    }

    /**
     * Item constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param ItemClient $itemClient
     * @param AttributeFactory $attributeFactory
     * @param ItemPropertyCollectionFactory $itemPropertyCollection
     * @param Item\VariationFactory $variationFactory
     * @param Item\MediaFactory $mediaFactory
     * @param Item\ShippingProfileFactory $shippingProfileFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ItemClient $itemClient,
        VariationClient $variationClient,
        AttributeFactory $attributeFactory,
        ItemPropertyCollectionFactory $itemPropertyCollection,
        Item\VariationFactory $variationFactory,
        Item\MediaFactory $mediaFactory,
        Item\ShippingProfileFactory $shippingProfileFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $itemClient;
        $this->_variationApi = $variationClient;
        $this->_attributeFactory = $attributeFactory;
        $this->_variationFactory = $variationFactory;
        $this->_propertyCollectionFactory = $itemPropertyCollection;
        $this->_mediaFactory = $mediaFactory;
        $this->_shippingProfileFactory = $shippingProfileFactory;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * @return ItemClient
     */
    protected function _api()
    {
        if (!$this->_api->getProfileEntity()) {
            $this->_api->setProfileEntity($this->getProfileEntity());
        }
        return $this->_api;
    }

    /**
     * @return VariationClient
     */
    protected function _variationApi()
    {
        return $this->_variationApi;
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    public function getMainVariationId()
    {
        return $this->getData(self::MAIN_VARIATION_ID);
    }

    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function getItemType()
    {
        return $this->getData(self::ITEM_TYPE);
    }

    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    public function getBundleType()
    {
        return $this->getData(self::BUNDLE_TYPE);
    }

    public function getStockType()
    {
        return $this->getData(self::STOCK_TYPE);
    }

    public function getAttributeSet()
    {
        return $this->getData(self::ATTRIBUTE_SET);
    }

    public function getFlagOne()
    {
        return $this->getData(self::FLAG_ONE);
    }

    public function getFlagTwo()
    {
        return $this->getData(self::FLAG_TWO);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getCustomTariffNo()
    {
        return $this->getData(self::CUSTOMS_TARIFF_NO);
    }

    public function getRevenueAccount()
    {
        return $this->getData(self::REVENUE_ACCOUNT);
    }

    public function getCondition()
    {
        return $this->getData(self::CONDITION);
    }

    public function getConditionApi()
    {
        return $this->getData(self::CONDITION_API);
    }

    public function getOwnerId()
    {
        return $this->getData(self::OWNER_ID);
    }

    public function getManufacturerId()
    {
        return $this->getData(self::MANUFACTURER_ID);
    }

    public function getManufacturerCountryId()
    {
        return $this->getData(self::MANUFACTURER_COUNTRY_ID);
    }

    public function getStoreSpecial()
    {
        return $this->getData(self::STORE_SPECIAL);
    }

    public function getCouponRestriction()
    {
        return $this->getData(self::COUPON_RESTRICTION);
    }

    public function getMaxOrderQty()
    {
        return $this->getData(self::MAX_ORDER_QTY);
    }

    public function getIsSubscription()
    {
        return $this->getData(self::IS_SUBSCRIPTION);
    }

    public function getRakutenCategoryId()
    {
        return $this->getData(self::RAKUTEN_CATEGORY_ID);
    }

    public function getIsShippingPackage()
    {
        return $this->getData(self::IS_SHIPPING_PACKAGE);
    }

    public function getIsSerialNumber()
    {
        return $this->getData(self::IS_SERIAL_NUMBER);
    }

    public function getAmazonFbaPlatform()
    {
        return $this->getData(self::AMAZON_FBA_PLATFORM);
    }

    public function getIsShippableByAmazon()
    {
        return $this->getData(self::IS_SHIPPABLE_BY_AMAZON);
    }

    public function getAmazonProductType()
    {
        return $this->getData(self::AMAZON_PRODUCT_TYPE);
    }

    public function getAgeRestriction()
    {
        return $this->getData(self::AGE_RESTRICTION);
    }

    public function getFeedBack()
    {
        return $this->getData(self::FEEDBACK);
    }

    public function getFree1()
    {
        return $this->getData(self::FREE1);
    }

    public function getFree2()
    {
        return $this->getData(self::FREE2);
    }

    public function getFree3()
    {
        return $this->getData(self::FREE3);
    }

    public function getFree4()
    {
        return $this->getData(self::FREE4);
    }

    public function getFree5()
    {
        return $this->getData(self::FREE5);
    }

    public function getFree6()
    {
        return $this->getData(self::FREE6);
    }

    public function getFree7()
    {
        return $this->getData(self::FREE7);
    }

    public function getFree8()
    {
        return $this->getData(self::FREE8);
    }

    public function getFree9()
    {
        return $this->getData(self::FREE9);
    }

    public function getFree10()
    {
        return $this->getData(self::FREE10);
    }

    public function getShippingProfiles()
    {
        return $this->getData(self::SHIPPING_PROFILE);
    }

    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param null $variationId
     * @return Item\Variation
     * @throws \Exception
     */
    public function getVariation($variationId = null)
    {
        $this->_variation = $this->_getVariationModel();
        $this->_variation->getResource()
            ->load($this->_variation, $variationId ? $variationId : $this->getVariationId(), self::VARIATION_ID);
        if (!$this->_variation->getId()) {
            throw new \Exception(__('Variation does not exist. [Variation id %1]', $this->getVariationId()));
        }
        return $this->_variation;
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getItemVariationCollection()
    {
        return $this->_getVariationModel()
            ->getItemVariationCollection($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Variation\Collection
     */
    public function getUsedVariations()
    {
        return $this->_getVariationModel()
            ->getItemLinkedVariations($this);
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Property\Collection
     */
    public function getItemProperties()
    {
        $collection = $this->_propertyCollectionFactory->create();
        $collection->addFieldToFilter(self::ITEM_ID, $this->getItemId());
        return $collection;
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\Media\Collection
     */
    public function getItemMediaImages()
    {
        return $this->__getMediaModel()
            ->getItemImages($this);
    }

    /**
     * @param $id
     * @return Attribute
     */
    public function getItemManufacturer($id)
    {
        $model = $this->__getAttributeModel();
        $model->getResource()
            ->load($model, (int) $id, 'manufacturer_id');

       return $model;
    }

    /**
     * @return \Plenty\Item\Model\ResourceModel\Import\Item\ShippingProfile\Collection
     */
    public function getItemShippingProfiles()
    {
       return $this->__getShippingProfileModel()
           ->getItemShippingProfiles($this);
    }


    public function getItemCrossSells()
    {}


    public function getItemEbayTitles()
    {}

    /**
     * @param $sku
     * @return DataObject
     * @throws \Exception
     */
    public function collectBySku($sku)
    {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        $response = new DataObject;
        $variationResponse = $this->_variationApi()
            ->getSearchVariations(
                null,
                null,
                null,
                $this->getProfileEntity()->getApiVariationSearchFilters(),
                null,
                null,
                null,
                $sku
            );

        if (!$variationResponse->getSize()) {
            return $response;
        }

        $itemId = $variationResponse->getFirstItem()->getData('item_id');
        $itemResponse = $this->_api()
            ->getSearchItems(null, (int) $itemId, $this->getProfileEntity()->getApiItemSearchFilters());

        if (!$itemResponse->getSize()) {
            return $response;
        }

        $this->_saveItemResponseData($itemResponse);
        $this->_saveVariationResponseData($variationResponse);

        $response->setData(self::ITEM_RESPONSE, $itemResponse->getFirstItem());
        $response->setData(self::VARIATION_RESPONSE,  $variationResponse->getFirstItem());

        $this->_getVariationModel()
            ->setProfileEntity($this->getProfileEntity())
            ->collect($itemId);

        return $response;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    private function _getLastUpdatedAt()
    {
        $updatedBetween = null;
        if ($this->getProfileEntity()->getApiBehaviour() !== Behaviour::APPEND) {
            return $updatedBetween;
        }

        /** @var Category $lastUpdatedEntry */
        $lastUpdatedEntry = $this->getCollection()
            ->addFieldToFilter('profile_id', $this->getProfileEntity()->getProfile()->getId())
            ->addFieldToSelect('collected_at')
            ->setOrder('collected_at', 'desc')
            ->setPageSize(1)
            ->getFirstItem();

        if (strtotime($lastUpdatedEntry->getCollectedAt()) > 0) {
            $updatedBetween = $this->_helper()
                ->getDateTimeLocale($lastUpdatedEntry->getCollectedAt());
        }

        return $updatedBetween;
    }

    /**
     * @return Attribute
     */
    private function __getAttributeModel()
    {
        if (!$this->_attributeModel) {
            $this->_attributeModel = $this->_attributeFactory->create();
        }
        return $this->_attributeModel;
    }

    /**
     * @return Item\Variation
     */
    private function _getVariationModel()
    {
        if (!$this->_variationModel) {
            $this->_variationModel = $this->_variationFactory->create();
        }
        return $this->_variationModel;
    }

    private function __getPropertiesModel()
    {
        if (!$this->_attributeModel) {
            $this->_attributeModel = $this->_attributeFactory->create();
        }
        return $this->_attributeModel;
    }

    /**
     * @return Item\Media
     */
    private function __getMediaModel()
    {
        if (!$this->_mediaModel) {
            $this->_mediaModel = $this->_mediaFactory->create();
        }
        return $this->_mediaModel;
    }

    /**
     * @return Item\ShippingProfile
     */
    private function __getShippingProfileModel()
    {
        if (!$this->_shippingProfileModel) {
            $this->_shippingProfileModel = $this->_shippingProfileFactory->create();
        }
        return $this->_shippingProfileModel;
    }

    /**
     * @param Collection $responseData
     * @return $this
     * @throws \Exception
     */
    private function _saveItemResponseData(Collection $responseData)
    {
        $this->getResource()->saveItems($responseData);
        return $this;
    }

    /**
     * @param Collection $responseData
     * @return $this
     * @throws \Exception
     */
    private function _saveVariationResponseData(Collection $responseData)
    {
        $this->_getVariationModel()
            ->getResource()->saveVariations($responseData);
        return $this;
    }




    /**
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _getLangParams()
    {
        $lang = null;
        return null;
        if ($this->isMultiStore()) {

            $stores = array();
            foreach ($this->_helper()->getStoreMapping() as $store) {
                $stores[] = $store['plenty_lang'];
            }

            $lang = implode(',', $stores);
        }

        return $lang;
    }


    /**
     * @param array $variationIds
     * @param $status
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus(array $variationIds, $status)
    {
        $bind = array('status' => $status);
        $where = array(
            'profile_id = ?' => $this->getProfileEntity()->getProfile()->getId(),
            'variation_id IN(?)' => $variationIds);
        $this->getResource()->update($bind, $where);
        return $this;
    }

    /**
     * Get all item ids
     *
     * @return array
     */
    public function getAllIds()
    {
        return $this->getCollection()
            ->addFieldToSelect('item_id')
            ->getColumnValues('item_id');
    }

    /**
     * @param $status
     * @param string $type (options: item_id || variation_id)
     *
     * @return mixed
     */
    public function getFilterIdsByStatus($status, $type = 'item_id')
    {
        return $this->getCollection()
            ->addFieldToFilter('status', $status)
            ->addFieldToSelect($type)
            ->getColumnValues($type);
    }
}