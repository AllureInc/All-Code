<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Bundle\Model\Product\Type as ProductTypeBundle;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

use Plenty\Item\Api\Data;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Item\Api\Data\Import\Item\MediaInterface;
use Plenty\Item\Api\Data\Import\Item\CrosssellsInterface;
use Plenty\Item\Api\Data\Import\Item\ShippingProfileInterface;
use Plenty\Item\Api\ItemRepositoryInterface;
use Plenty\Item\Api\Data\Import\ItemSearchResultsInterfaceFactory;
use Plenty\Item\Rest\AbstractData\ItemDataInterface;
use Plenty\Item\Rest\Response\Item\MediaDataInterface;
use Plenty\Item\Rest\Response\Item\CrossSellsDataInterface;
use Plenty\Item\Rest\Response\Item\ShippingProfileDataInterface;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Core\Model\Source\Status;

/**
 * Class ItemRepository
 * @package Plenty\Item\Model\Import
 */
class ItemRepository implements ItemRepositoryInterface
{
    /**
     * @var ResourceModel\Import\Item
     */
    private $_resource;

    /**
     * @var ResourceModel\Import\Item\Media
     */
    private $_mediaResource;

    /**
     * @var ResourceModel\Import\Item\Texts
     */
    private $_textsResource;

    /**
     * @var ResourceModel\Import\Item\Crosssells
     */
    private $_crosssellsResource;

    /**
     * @var ResourceModel\Import\Item\ShippingProfile
     */
    private $_shippingProfileResource;

    /**
     * @var ItemFactory
     */
    private $_itemFactory;

    /**
     * @var ResourceModel\Import\Item\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var ItemSearchResultsInterfaceFactory
     */
    private $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var Json
     */
    private $_dateTime;

    /**
     * @var Json|null
     */
    private $_serializer;

    /**
     * ItemRepository constructor.
     * @param ResourceModel\Import\Item $resource
     * @param ResourceModel\Import\Item\Media $mediaResource
     * @param ResourceModel\Import\Item\Texts $textsResource
     * @param ResourceModel\Import\Item\Crosssells $crosssellsResource
     * @param ResourceModel\Import\Item\ShippingProfile $shippingProfileResource
     * @param ItemFactory $itemFactory
     * @param ResourceModel\Import\Item\CollectionFactory $collectionFactory
     * @param ItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Import\Item $resource,
        ResourceModel\Import\Item\Media $mediaResource,
        ResourceModel\Import\Item\Texts $textsResource,
        ResourceModel\Import\Item\Crosssells $crosssellsResource,
        ResourceModel\Import\Item\ShippingProfile $shippingProfileResource,
        ItemFactory $itemFactory,
        ResourceModel\Import\Item\CollectionFactory $collectionFactory,
        ItemSearchResultsInterfaceFactory $searchResultsFactory,
        Helper $helper,
        DateTime $dateTime,
        ?Json $serializer = null,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_mediaResource = $mediaResource;
        $this->_textsResource = $textsResource;
        $this->_crosssellsResource = $crosssellsResource;
        $this->_shippingProfileResource = $shippingProfileResource;
        $this->_itemFactory = $itemFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\Import\ItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Import\Item\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var ItemSearchResultsInterfaceFactory $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param $entityId
     * @param null $field
     * @return ItemInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var ItemInterface $item */
        $item = $this->_itemFactory->create();
        $this->_resource->load($item, $entityId, $field);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('The item with ID "%1" doesn\'t exist.', $entityId));
        }
        return $item;
    }

    /**
     * @param $itemId
     * @return ItemInterface
     * @throws NoSuchEntityException
     */
    public function getByItemId($itemId)
    {
        return $this->get($itemId, ItemInterface::ITEM_ID);
    }

    /**
     * @param $variationId
     * @return ItemInterface
     * @throws NoSuchEntityException
     */
    public function getByVariationId($variationId)
    {
        return $this->get($variationId, ItemInterface::VARIATION_ID);
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemInterface $item)
    {
        try {
            $this->_resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $item;
    }

    /**
     * @param array $data
     * @param array $fields
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $data, array $fields = [])
    {
        try {
            $this->_resource->addMultiple($data, $fields);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws CouldNotSaveException
     */
    public function update(array $bind, $where = '')
    {
        try {
            $this->_resource->update($bind, $where);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param ItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\Import\ItemInterface $item)
    {
        try {
            $this->_resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $entityId
     * @return bool|ItemInterface
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }

    /**
     * @param null $profileId
     * @return string|null
     * @throws \Exception
     */
    public function getLastUpdatedAt($profileId = null)
    {
        $updatedBetween = null;

        /** @var ResourceModel\Import\Item\Collection $collection */
        $collection = $this->_collectionFactory->create();
        if (null !== $profileId) {
            $collection->addFieldToFilter('profile_id', $profileId);
        }
        $collection->addFieldToSelect('collected_at')
            ->setOrder('collected_at', 'desc')
            ->setPageSize(1);

        /** @var ItemInterface $item */
        $item = $collection->getFirstItem();

        if (strtotime($item->getCollectedAt()) > 0) {
            $updatedBetween = $this->_helper->getDateTimeLocale($item->getCollectedAt());
        }

        return $updatedBetween;
    }

    /**
     * @param $profileId
     * @param Collection $responseData
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function saveItemCollection($profileId, Collection $responseData)
    {
        $items =
        $itemTexts =
        $itemCrossSells =
        $itemMedia =
        $itemShippingProfiles = [];
        /** @var ItemInterface $item */
        foreach ($responseData as $item) {
            $itemId = $item->getItemId();
            $variationId = $item->getMainVariationId();
            $sku = null;
            $externalId = null;
            $mainVariationIndex = null;

            if ($itemVariations = $item->getData(ItemInterface::ITEM_VARIATIONS)) {
                $index = array_search($item->getItemId(), array_column($itemVariations, ItemDataInterface::ITEM_ID));
                if ($index !== false) {
                    $mainVariationIndex = $index;
                }
            }

            if (null !== $mainVariationIndex) {
                $sku = isset($itemVariations[$mainVariationIndex]['number'])
                    ? $itemVariations[$mainVariationIndex]['number']
                    : null;
                $externalId = isset($itemVariations[$mainVariationIndex]['externalId'])
                    ? $itemVariations[$mainVariationIndex]['externalId']
                    : null;
            }

            $item->setData(ItemInterface::PROFILE_ID, $profileId);
            $item->setData(ItemInterface::VARIATION_ID, $variationId);
            $item->setData(ItemInterface::SKU, $sku);
            $item->setData(ItemInterface::EXTERNAL_ID,$externalId);
            $item->setData('main_variation_column_index', (int) $mainVariationIndex);

            // HANDLE ITEM DATA
            $items[] = $this->_prepareItemData($item);

            // HANDLE ITEM CROSS-SELLS DATA
            if ($crossSells = $item->getData(ItemInterface::ITEM_CROSS_SELLING)) {
                foreach ($crossSells as $crossSell) {
                    $crossSell[ItemInterface::ITEM_ID] = $itemId;
                    $crossSell[ItemInterface::VARIATION_ID] = $variationId;
                    $crossSell[ItemInterface::EXTERNAL_ID] = $externalId;
                    $crossSell[ItemInterface::SKU] = $sku;
                    $itemCrossSells[] = $this->_prepareCrossSellsData($crossSell);
                }
            }

            // HANDLE ITEM MEDIA DATA
            if ($images = $item->getData(ItemInterface::ITEM_IMAGES)) {
                foreach ($images as $image) {
                    $image[ItemInterface::ITEM_ID] = $itemId;
                    $image[ItemInterface::VARIATION_ID] = $variationId;
                    $image[ItemInterface::EXTERNAL_ID] = $externalId;
                    $image[ItemInterface::SKU] = $sku;
                    $itemMedia[] = $this->_prepareMediaData($image);
                }
                $this->_handleMediaRecord($itemId, $images);
            } else {
                $this->_handleMediaRecord($itemId, []);
            }

            // HANDLE ITEM SHIPPING PROFILE DATA
            if ($shippingProfiles = $item->getData(ItemInterface::ITEM_SHIPPING_PROFILES)) {
                foreach ($shippingProfiles as $shippingProfile) {
                    $shippingProfile[ItemInterface::ITEM_ID] = $itemId;
                    $shippingProfile[ItemInterface::VARIATION_ID] = $variationId;
                    $shippingProfile[ItemInterface::EXTERNAL_ID] = $externalId;
                    $shippingProfile[ItemInterface::SKU] = $sku;
                    $itemShippingProfiles[] = $this->_prepareShippingProfileData($shippingProfile);
                }
            }
        }

        try {
            $this->_resource->addMultiple($items);

            // Save item texts
            if (!empty($itemTexts)) {
                $this->_textsResource->addMultiple($itemTexts);
            }

            // Save item cross-sells
            if (!empty($itemCrossSells)) {
                $this->_crosssellsResource->addMultiple($itemCrossSells);
            }

            // Save item media
            if (!empty($itemMedia)) {
                $this->_mediaResource->addMultiple($itemMedia);
            }

            // Save item shipping profiles
            if (!empty($itemShippingProfiles)) {
                $this->_shippingProfileResource->addMultiple($itemShippingProfiles);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param ItemInterface $item
     * @return array
     */
    private function _prepareItemData($item)
    {
        if (!$profileId = $item->getProfileId()) {
            return [];
        }

        $isActive = false;
        $productType = ProductType::TYPE_SIMPLE;
        $bundleType = null;

        if (null !== $item->getData('main_variation_column_index')
            && $variations = $item->getData(ItemInterface::ITEM_VARIATIONS)
        ) {
            $mainVariationIndex = $item->getData('main_variation_column_index');
            $isActive = $variations[$mainVariationIndex][ItemDataInterface::IS_ACTIVE] ?? false;
            if (count($variations) > 1) {
                $productType = ConfigurableProduct::TYPE_CODE;
            }
            if (isset($variations[$mainVariationIndex][ItemDataInterface::BUNDLE_TYPE])) {
                $bundleType = $variations[$mainVariationIndex][ItemDataInterface::BUNDLE_TYPE];
                $productType = $bundleType === ProductTypeBundle::TYPE_CODE
                    ? ProductTypeBundle::TYPE_CODE
                    : ProductType::TYPE_SIMPLE;
            }
        }

        return [
            ItemInterface::PROFILE_ID => $profileId,
            ItemInterface::ITEM_ID => $item->getItemId(),
            ItemInterface::VARIATION_ID => $item->getMainVariationId(),
            ItemInterface::EXTERNAL_ID => $item->getExternalId(),
            ItemInterface::SKU => $item->getSku(),
            ItemInterface::STATUS => Status::PENDING,
            ItemInterface::IS_ACTIVE => $isActive,
            ItemInterface::ITEM_TYPE => $item->getItemType(),
            ItemInterface::PRODUCT_TYPE => $productType,
            ItemInterface::BUNDLE_TYPE => $bundleType,
            ItemInterface::STOCK_TYPE => $item->getStockType(),
            ItemInterface::ATTRIBUTE_SET => $item->getAttributeSet(),
            ItemInterface::FLAG_ONE => $item->getFlagOne(),
            ItemInterface::FLAG_TWO => $item->getFlagTwo(),
            ItemInterface::POSITION => $item->getPosition(),
            ItemInterface::CUSTOMS_TARIFF_NO => $item->getCustomTariffNo(),
            ItemInterface::REVENUE_ACCOUNT => $item->getRevenueAccount(),
            ItemInterface::CONDITION => $item->getCondition(),
            ItemInterface::CONDITION_API => $item->getConditionApi(),
            ItemInterface::OWNER_ID => $item->getOwnerId(),
            ItemInterface::MANUFACTURER_ID => $item->getManufacturerId(),
            ItemInterface::MANUFACTURER_COUNTRY_ID => $item->getManufacturerCountryId(),
            ItemInterface::STORE_SPECIAL => $item->getStoreSpecial(),
            ItemInterface::COUPON_RESTRICTION => $item->getCouponRestriction(),
            ItemInterface::MAX_ORDER_QTY => $item->getMaxOrderQty(),
            ItemInterface::IS_SUBSCRIPTION => $item->getIsSubscription(),
            ItemInterface::RAKUTEN_CATEGORY_ID => $item->getRakutenCategoryId(),
            ItemInterface::IS_SHIPPING_PACKAGE => $item->getIsShippingPackage(),
            ItemInterface::IS_SERIAL_NUMBER => $item->getIsSerialNumber(),
            ItemInterface::AMAZON_FBA_PLATFORM => $item->getAmazonFbaPlatform(),
            ItemInterface::IS_SHIPPABLE_BY_AMAZON  => $item->getIsShippableByAmazon(),
            ItemInterface::AMAZON_PRODUCT_TYPE => $item->getAmazonProductType(),
            ItemInterface::AGE_RESTRICTION => $item->getAgeRestriction(),
            ItemInterface::FEEDBACK => $item->getFeedBack(),
            ItemInterface::FREE1 => $item->getFree1(),
            ItemInterface::FREE2 => $item->getFree2(),
            ItemInterface::FREE3 => $item->getFree3(),
            ItemInterface::FREE4 => $item->getFree4(),
            ItemInterface::FREE5 => $item->getFree5(),
            ItemInterface::FREE6 => $item->getFree6(),
            ItemInterface::FREE7 => $item->getFree7(),
            ItemInterface::FREE8 => $item->getFree8(),
            ItemInterface::FREE9 => $item->getFree9(),
            ItemInterface::FREE10 => $item->getFree10(),
            ItemInterface::CREATED_AT => $item->getCreatedAt(),
            ItemInterface::UPDATED_AT => $item->getUpdatedAt(),
            ItemInterface::COLLECTED_AT => $this->_dateTime->gmtDate(),
            ItemInterface::MESSAGE => __('Collected.')
        ];
    }

    /**
     * @param array $crossSells
     * @return array
     */
    private function _prepareCrossSellsData(array $crossSells)
    {
        return [
            ItemInterface::ITEM_ID => $crossSells[ItemInterface::ITEM_ID] ?? null,
            ItemInterface::VARIATION_ID => $crossSells[CrosssellsInterface::VARIATION_ID] ?? null,
            ItemInterface::EXTERNAL_ID => $crossSells[ItemInterface::EXTERNAL_ID] ?? null,
            ItemInterface::SKU => $crossSells[ItemInterface::SKU] ?? null,
            CrosssellsInterface::ITEM_CROSSSELLS_ID => $crossSells[CrossSellsDataInterface::CROSS_ITEM_ID] ?? null,
            CrosssellsInterface::RELATIONSHIP => $crossSells[CrossSellsDataInterface::RELATIONSHIP] ?? null,
            CrosssellsInterface::IS_DYNAMIC => $crossSells[CrossSellsDataInterface::IS_DYNAMIC] ?? null,
            CrosssellsInterface::CREATED_AT => $crossSells[CrossSellsDataInterface::CREATED_AT] ?? null,
            CrosssellsInterface::UPDATED_AT => $crossSells[CrossSellsDataInterface::UPDATED_AT] ?? null,
            CrosssellsInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $media
     * @return array
     */
    private function _prepareMediaData(array $media)
    {
        return [
            ItemInterface::ITEM_ID => $media[MediaInterface::ITEM_ID] ?? null,
            ItemInterface::VARIATION_ID => $media[CrosssellsInterface::VARIATION_ID] ?? null,
            ItemInterface::EXTERNAL_ID => $media[CrosssellsInterface::EXTERNAL_ID] ?? null,
            ItemInterface::SKU =>  $media[CrosssellsInterface::SKU] ?? null,
            MediaInterface::MEDIA_ID => $media[MediaDataInterface::ID] ?? null,
            MediaInterface::MEDIA_TYPE => MediaInterface::MEDIA_TYPE_ITEM,
            MediaInterface::TYPE => $media[MediaDataInterface::TYPE] ?? null,
            MediaInterface::FILE_TYPE => $media[MediaDataInterface::FILE_TYPE] ?? null,
            MediaInterface::PATH => $media[MediaDataInterface::PATH] ?? null,
            MediaInterface::POSITION => $media[MediaDataInterface::POSITION] ?? null,
            MediaInterface::MD5_CHECKSUM => $media[MediaDataInterface::MD5_CHECKSUM] ?? null,
            MediaInterface::MD5_CHECKSUM_ORIGINAL => $media[MediaDataInterface::MD5_CHECKSUM_ORIGINAL] ?? null,
            MediaInterface::WIDTH => $media[MediaDataInterface::WIDTH] ?? null,
            MediaInterface::HEIGHT => $media[MediaDataInterface::HEIGHT] ?? null,
            MediaInterface::SIZE => $media[MediaDataInterface::SIZE] ?? null,
            MediaInterface::STORAGE_PROVIDER_ID => $media[MediaDataInterface::STORAGE_PROVIDER_ID] ?? null,
            MediaInterface::CLEAN_IMAGE_NAME => $media[MediaDataInterface::CLEAN_IMAGE_NAME] ?? null,
            MediaInterface::URL => $media[MediaDataInterface::URL] ?? null,
            MediaInterface::URL_MIDDLE => $media[MediaDataInterface::URL_MIDDLE] ?? null,
            MediaInterface::URL_PREVIEW => $media[MediaDataInterface::URL_PREVIEW] ?? null,
            MediaInterface::URL_SECONDARY_PREVIEW => $media[MediaDataInterface::URL_SECONDARY_PREVIEW] ?? null,
            MediaInterface::DOCUMENT_UPLOAD_PATH => $media[MediaDataInterface::DOCUMENT_UPLOAD_PATH] ?? null,
            MediaInterface::DOCUMENT_UPLOAD_PATH_PREVIEW => $media[MediaDataInterface::DOCUMENT_UPLOAD_PATH_PREVIEW] ?? null,
            MediaInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH => $media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_WIDTH] ?? null,
            MediaInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT => $media[MediaDataInterface::DOCUMENT_UPLOAD_PREVIEW_HEIGHT] ?? null,
            MediaInterface::AVAILABILITIES => isset($media[MediaDataInterface::AVAILABILITIES])
                ? $this->_serializer->serialize($media[MediaDataInterface::AVAILABILITIES])
                : null,
            MediaInterface::NAMES => isset($media[MediaDataInterface::NAMES])
                ? $this->_serializer->serialize($media[MediaDataInterface::NAMES])
                : null,
            MediaInterface::CREATED_AT => $media[MediaDataInterface::CREATED_AT] ?? null,
            MediaInterface::UPDATED_AT => $media[MediaDataInterface::UPDATED_AT] ?? null,
            MediaInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $crossSells
     * @return array
     */
    private function _prepareShippingProfileData(array $crossSells)
    {
        return [
            ShippingProfileInterface::ITEM_ID => $crossSells[ShippingProfileInterface::ITEM_ID] ?? null,
            ShippingProfileInterface::VARIATION_ID => $crossSells[CrosssellsInterface::VARIATION_ID] ?? null,
            ShippingProfileInterface::EXTERNAL_ID => $crossSells[ShippingProfileInterface::EXTERNAL_ID] ?? null,
            ShippingProfileInterface::SKU => $crossSells[ShippingProfileInterface::SKU] ?? null,
            ShippingProfileInterface::PLENTY_ENTITY_ID => $crossSells[ShippingProfileDataInterface::ID] ?? null,
            ShippingProfileInterface::PROFILE_ID => $crossSells[ShippingProfileDataInterface::PROFILE_ID] ?? null,
            ShippingProfileInterface::CREATED_AT => $crossSells[ShippingProfileDataInterface::CREATED_AT] ?? null,
            ShippingProfileInterface::UPDATED_AT => $crossSells[ShippingProfileDataInterface::UPDATED_AT] ?? null,
            ShippingProfileInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param $itemId
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _handleMediaRecord($itemId, array $data)
    {
        if (empty($data)) {
            $this->_mediaResource->removeEntry($itemId, MediaInterface::ITEM_ID);
        } else {
            $existingRecord = $this->_mediaResource->lookupItemMediaRecords($itemId);
            $delete = array_diff($existingRecord, array_column($data, ItemDataInterface::ID));
            if (!empty($delete)) {
                $this->_mediaResource->removeEntry($delete, MediaInterface::MEDIA_ID);
            }
        }

        return $this;
    }
}
