<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductItemExportManagementInterface;
use Plenty\Item\Model\Import\AttributeFactory;
use Plenty\Item\Model\Import\Item\MediaFactory;
use Plenty\Item\Rest\Item as ItemClient;
use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;

/**
 * Class ProductItemExportManagement
 * @package Plenty\Item\Profile
 */
class ProductItemExportManagement extends AbstractManagement
    implements ProductItemExportManagementInterface
{
    /**
     * @var AttributeFactory
     */
    private $_attributeFactory;

    /**
     * @var MediaFactory
     */
    private $_mediaFactory;

    /**
     * @var Product\ActionFactory
     */
    private $_productActionFactory;

    /**
     * ProductItemExportManagement constructor.
     * @param ItemClient $itemClient
     * @param AttributeFactory $attributeFactory
     * @param MediaFactory $mediaFactory
     * @param Product\ActionFactory $productActionFactory
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ItemClient $itemClient,
        AttributeFactory $attributeFactory,
        MediaFactory $mediaFactory,
        Product\ActionFactory $productActionFactory,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_client = $itemClient;
        $this->_attributeFactory = $attributeFactory;
        $this->_mediaFactory = $mediaFactory;
        $this->_productActionFactory = $productActionFactory;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @return ProductExportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): ProductExportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        return $this->_profileEntity;
    }

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity)
    {
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return HistoryInterface
     * @throws \Exception
     */
    public function getProfileHistory() : HistoryInterface
    {
        if (!$this->_profileHistory) {
            throw new \Exception(__('Profile history is not set.'));
        }

        return $this->_profileHistory;
    }

    /**
     * @param HistoryInterface $history
     * @return $this|mixed
     */
    public function setProfileHistory(HistoryInterface $history)
    {
        $this->_profileHistory = $history;
        return $this;
    }

    /**
     * @param Product $product
     * @return $this|ProductItemExportManagementInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute(Product $product)
    {
        $this->_initResponseData();

        $response = $this->_createItem($product);
        if (empty($response)) {
            throw new \Exception(__('Could not initialise item response. [SKU: %1]', $product->getSku()));
        }

        $this->_addMedia($product, $response);

        return $this;
    }

    /**
     * @param Product $product
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _createItem(Product $product)
    {
        if (!$categories = $product->getData(ProductExportManagementInterface::REQUEST_CATEGORY)) {
            if ($categoryMapping = $this->getProfileEntity()->getRootCategoryMapping()) {
                $categories[] = ['categoryId' => current($categoryMapping)];
            }
        }

        if (empty($categories)) {
            throw new \Exception(__('Product categories are not set. [SKU: %1]', $product->getSku()));
        }

        $variationRequest = [
            [
                'variationCategories' => $categories,
                'unit' => [
                    'unitId' => 1,
                    'content' => 1
                ]
            ]
        ];

        $request = array(
            'position'              => 1,
            'stockType'             => 0,
            'storeSpecial'          => 2,
            'condition'             => 0,
            'feedback'              => 0,
            'isSubscribable'        => false,
            'isShippingPackage'     => false,
            'itemType'              => 'default',
            'variations'            => $variationRequest
        );


        $manufacturerMapping = $this->getProfileEntity()->getManufacturerMapping();
        if ($manufacturerMapping
            && $manufacturerName = $product->getAttributeText($manufacturerMapping)
        ) {
            $attributeModel = $this->_attributeFactory->create();
            $manufacturerId = $attributeModel->getManufacturerByName($manufacturerName);
            $manufacturerId ? $request['manufacturerId'] = $manufacturerId : null;
        }

        if ($flagOne = $this->getProfileEntity()->getFlagOne()) {
            $request['flagOne'] = $flagOne;
        }

        if ($flagTwo = $this->getProfileEntity()->getFlagTwo()) {
            $request['flagTwo'] = $flagTwo;
        }

        $itemId = $product->getData(ProductInterface::PLENTY_ITEM_ID);
        $response = $this->_client->createItem($request, $itemId);

        if (!$itemId
            && isset($response['id'])
            && isset($response['mainVariationId'])
        ) {
            $product->setData(ProductInterface::PLENTY_ITEM_ID, (int) $response['id']);
            $product->setData(ProductInterface::PLENTY_VARIATION_ID, (int) $response['mainVariationId']);
            $this->_updateProductPlentyData($product);
        }

        $this->_response['success'][] = __('Item has been %1. [SKU: %2, Plenty Item ID: %3]',
            $itemId ? 'updated' : 'created',
            $product->getSku(),
            $response['id']
        );

        return $response;
    }

    /**
     * @param Product $product
     * @param array $response
     * @return $this
     * @throws \Exception
     */
    protected function _addMedia(Product $product, array $response)
    {
        if (empty($response)
            || !isset($response['id'])
            || !$this->getProfileEntity()->getIsActiveExportMedia()
            || !$productImages = $product->getMediaGalleryImages()
        ) {
            return $this;
        }

        $mediaModel = $this->_mediaFactory->create();
        $itemImages = $mediaModel->getItemImagesByItemId($response['id'])
            ->getData();

        $newImages = [];
        foreach ($productImages as $image) {
            $imageId = null;
            $fileName = str_replace('_', '-', basename($image->getUrl()));

            $index = $this->getSearchArrayMatch($fileName, $itemImages, 'clean_image_name');
            if (false !== $index && isset($itemImages[$index])) {
                $this->_response['success'][] = __('Product image exists. [SKU: %1, Image: %2]', $product->getSku(), $fileName);
                unset($itemImages[$index]);
                $itemImages = array_values($itemImages);
                continue;
            }

            $image->setData('new_image', $fileName);
            $newImages[] = $image;
        }

        $this->_removeImages($product, $itemImages)
            ->_createImages($product, $newImages);

        return $this;
    }

    /**
     * @param Product $product
     * @param array $images
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _removeImages(Product $product, array $images)
    {
        if (empty($images)) {
            return $this;
        }

        $request = [];
        $removedIds = [];
        foreach ($images as $image) {
            $removedIds[] = $image['entity_id'];
            $request['payloads'][] = [
                'resource' => "/rest/items/{$image['item_id']}/images/{$image['media_id']}",
                'method' => 'DELETE',
                'body' => []
            ];
        }

        $mediaModel = $this->_mediaFactory->create();
        $response = $this->_client->getBatchProcess($request);
        if (empty($response)) {
            $this->_response['error'][] = __( 'Failed removing product images. [SKU: %1]', $product->getSku());
        } else {
            try {
                $mediaModel->getResource()
                    ->removeEntry($removedIds);
            } catch (\Exception $e) {
                $this->_response['error'][] = __('Failed exporting product images. [SKU: %1]. Reason: %2',
                    $product->getSku(), $e->getMessage());
            }
            $this->_response['success'][] = __('Product images have been removed. [SKU: %1]', $product->getSku());
        }

        return $this;
    }

    /**
     * @param Product $product
     * @param array $images
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _createImages(Product $product, array $images)
    {
        if (empty($images)) {
            return $this;
        }

        $ctx = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        $request = [];

        /** @var DataObject $image */
        foreach ($images as $image) {
            if (!$fileName = $image->getData('new_image')) {
                continue;
            }
            $imageContent = @base64_encode(@file_get_contents($image->getUrl(), false, stream_context_create($ctx)));
            $request['payloads'][] = [
                'resource' => "/rest/items/{$product->getData(ProductInterface::PLENTY_ITEM_ID)}/images/upload",
                'method' => 'POST',
                'body' => [
                    'position' => $image->getPosition(),
                    // 'fileType' => $this->_getFileType($image->getFile()),
                    // 'uploadUrl' => $image->getUrl(),
                    'uploadFileName' => $fileName,
                    'uploadImageData' => $imageContent,
                    'names' => [
                        [
                            'lang' => $product->getData('default_lang'),
                            'name' => $image->getLabel() ?? $product->getName(),
                            'alternate' => $image->getLabel() ?? $product->getName()
                        ]
                    ],
                    'availabilities' => [
                        [
                            'type' => $this->getProfileEntity()->getMediaFilter(),
                            'value' => $this->_helper->getPlentyId()
                        ]
                    ]
                ]
            ];
        }

        $response = $this->_client->getBatchProcess($request);
        if (empty($response)) {
            $this->_response['error'][] = __('Failed creating product images. [SKU: %1]', $product->getSku());
        } else {
            $this->_response['success'][] = __('Product images have been created. [SKU: %1]', $product->getSku());
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response = null;
        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    private function _updateProductPlentyData(Product $product)
    {
        /** @var Product\Action $productAction */
        $productAction = $this->_productActionFactory->create();

        $productAction->updateAttributes([$product->getId()], [
            ProductInterface::PLENTY_ITEM_ID => $product->getData(ProductInterface::PLENTY_ITEM_ID),
            ProductInterface::PLENTY_VARIATION_ID => $product->getData(ProductInterface::PLENTY_VARIATION_ID),
        ], 0);

        return $this;
    }
}