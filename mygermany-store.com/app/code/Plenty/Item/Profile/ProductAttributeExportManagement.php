<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Item\Api\ProductExportManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\ProductAttributeExportManagementInterface;
use Plenty\Item\Model\Import\Attribute;
use Plenty\Item\Rest\Attribute as AttributeClient;
use Plenty\Item\Helper;
use Plenty\Item\Model\Logger;

/**
 * Class AttributeExportManagement
 * @package Plenty\Item\Profile
 */
class ProductAttributeExportManagement extends AbstractManagement
    implements ProductAttributeExportManagementInterface
{
    /**
     * @var AttributeClient
     */
    private $_attributeClient;

    /**
     * @var Attribute
     */
    protected $_attributeModel;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $_productAttributeRepository;

    /**
     * ProductAttributeExportManagement constructor.
     * @param Attribute $attributeModel
     * @param AttributeClient $attributeClient
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Attribute $attributeModel,
        AttributeClient $attributeClient,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_attributeClient = $attributeClient;
        $this->_attributeModel = $attributeModel;
        $this->_productAttributeRepository = $productAttributeRepository;
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
     * @param ProductInterface $product
     * @return $this|ProductAttributeExportManagementInterface
     * @throws \Exception
     */
    public function execute(ProductInterface $product)
    {
        if ($product->getTypeId() != ConfigurableProduct::TYPE_CODE) {
            return $this;
        }

        $this->_initResponseData();

        /** @var ConfigurableProduct $configTypeInstance */
        $configTypeInstance = $product->getTypeInstance();
        $configAttributes = $configTypeInstance->getConfigurableAttributesAsArray($product);

        $attributes = [];
        foreach ($configAttributes as $configAttribute) {
            try {
                if (!$attributeId = $this->_exportAttribute($configAttribute)) {
                    continue;
                }

                $attributes[$attributeId] = [
                    'id' => $attributeId,
                    'backendName' => $configAttribute['attribute_code']
                ];

                // Add attribute store names
                if ($attributeName = $this->_exportAttributeNames($attributeId, $configAttribute)) {
                    $attributes[$attributeId]['names'] = $attributeName;
                }

                // Add attribute values & value names
                if ($attributeValues = $this->_exportAttributeValues($attributeId, $configAttribute['values'])) {
                    $attributes[$attributeId]['values'] = $attributeValues;
                }
            } catch (\Exception $e) {
                $this->addResponse($e->getMessage());
            }
        }

        $this->setResponse($attributes);
        $product->setData(ProductExportManagementInterface::CONFIG_ATTRIBUTES, $this->getResponse());

        return $this;
    }

    /**
     * @param array $data
     * @return bool|null
     * @throws \Exception
     */
    protected function _exportAttribute(array $data)
    {
        if (!isset($data['attribute_code'])) {
            return false;
        }

        if ($attributeId = $this->_getAttributeIdByCode($data['attribute_code'])) {
            return $attributeId[0] ?? null;
        }

        $params = [
            'backendName' => $data['attribute_code'],
            'position' => $data['position'] ?? 0
        ];

        $response = $this->_attributeClient->createAttribute($params);

        return isset($response['id'])
            ? $response['id']
            : null;
    }

    /**
     * @param $attributeId
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _exportAttributeNames($attributeId, array $data)
    {
        $response = [];
        if (!$attributeId || !isset($data['attribute_code'])) {
            return $response;
        }

        if (!$defaultStoreMapping = $this->getProfileEntity()->getDefaultStoreMapping()) {
            throw new \Exception(__('Stores are not mapped or default store is not set.'));
        }

        $attributeNames = $this->_attributeModel->getAttributeNamesById($attributeId);

        /** @var ProductAttributeInterface $productAttribute */
        $productAttribute = $this->_productAttributeRepository->get($data['attribute_code']);

        $plentyStore = $defaultStoreMapping->getData(ProductExportInterface::PLENTY_STORE);
        $name = $productAttribute->getDefaultFrontendLabel();

        $request = [
            'lang' => $plentyStore,
            'name' => $name
        ];

        $index = $this->getSearchArrayMatch($plentyStore, $attributeNames, 'lang');
        if (false !== $index
            && isset($attributeNames[$index]['name'])
        ) {
            if ($attributeNames[$index]['name'] === $name) {
                $response[$plentyStore] = $attributeNames[$index];
            } else {
                $response[$plentyStore] = $this->_attributeClient->createAttributeNames($request, $attributeId, $plentyStore);
            }
            return $request;
        }

        $data = $this->_attributeClient->createAttributeNames($request, $attributeId);
        if (isset($data['lang'])) {
            $response[$data['lang']] = $data;
        }

        return $response;
    }

    /**
     * @param $attributeId
     * @param array $values
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function _exportAttributeValues($attributeId, array $values)
    {
        $response = [];
        if (!$attributeId || empty($values)) {
            return $response;
        }

        $attributeValues = $this->_attributeModel->getAttributeValuesById($attributeId);
        foreach ($values as $value) {
            if (!isset($value['label']) || !isset($value['value_index'])) {
                continue;
            }

            $request = [
                'attributeId' => $attributeId,
                'backendName' => $value['label']
            ];

            $index = $this->getSearchArrayMatch($value['label'], $attributeValues, 'backendName');
            // Use existing record
            if (false !== $index
                && isset($attributeValues[$index]['id'])
                && isset($attributeValues[$index]['backendName'])
                && $attributeValues[$index]['backendName'] === $value['label']
                && $valueId = $attributeValues[$index]['id']
            ) {
                $response[$valueId] = [
                    'id' => $valueId,
                    'attributeId' => $attributeId,
                    'backendName' => $value['label'],
                    'value_index' => $value['value_index']
                ];

                $valueNames = $this->_exportAttributeValueNames($attributeId, $valueId, $value);
                if (empty($valueNames)) {
                    continue;
                }

                $response[$valueId]['value_names'] = [
                    'value_id' => $valueId,
                    'backendName' => $value['label'],
                    'names' => $valueNames
                ];

                continue;
            }

            // Create new record
            $valueResponse = $this->_attributeClient->createAttributeValues($request, $attributeId);
            if (!isset($valueResponse['id'])
                || !isset($valueResponse['backendName'])
                || !$valueId = $valueResponse['id']
            ) {
                throw new \Exception(__('Could not retrieve attribute value response data.'));
            }

            $response[$valueId] = [
                'id' => $valueId,
                'attributeId' => $attributeId,
                'backendName' => $valueResponse['backendName'],
                'value_index' => $value['value_index']
            ];

            $valueNames = $this->_exportAttributeValueNames($attributeId, $valueId, $value);
            if (empty($valueNames)) {
                continue;
            }

            $response[$valueId]['value_names'] = [
                'value_id' => $valueId,
                'backendName' => $valueResponse['backendName'],
                'names' => $valueNames
            ];
        }

        return $response;
    }

    /**
     * @param $attributeId
     * @param $attributeValueId
     * @param array $value
     * @return array|bool
     * @throws \Exception
     */
    protected function _exportAttributeValueNames($attributeId, $attributeValueId, array $value)
    {
        $response = [];
        if (!$attributeId || !$attributeValueId || !isset($value['label'])) {
            return $response;
        }

        if (!$defaultStoreMapping = $this->getProfileEntity()->getDefaultStoreMapping()) {
            throw new \Exception(__('Stores are not mapped or default store is not set.'));
        }

        $attributeValueNames = $this->_attributeModel->getAttributeValueNamesById($attributeId, $attributeValueId);
        $plentyStore = $defaultStoreMapping->getData(ProductExportInterface::PLENTY_STORE);

        $request = [
            'valueId' => $attributeValueId,
            'lang' => $plentyStore,
            'name' => $value['label']
        ];

        $index = $this->getSearchArrayMatch($plentyStore, $attributeValueNames, 'lang');

        if (false !== $index
            && isset($attributeValueNames[$index]['lang'])
            && isset($attributeValueNames[$index]['name'])
        ) {
            // Use existing attribute option value
            if ($attributeValueNames[$index]['name'] === $value['label']) {
                $response[$plentyStore] = $value['label'];
            } // Update with new attribute option value
            else {
                $valueNamesResponse = $this->_attributeClient->createAttributeValuesNames($request, $attributeValueId, $plentyStore);
                if (isset($valueNamesResponse['lang'])) {
                    $response[$valueNamesResponse['lang']] = $valueNamesResponse['name'] ?? null;
                }
            }

            return $response;
        }

        // Create new record
        $valueNamesResponse = $this->_attributeClient->createAttributeValuesNames($request, $attributeValueId);
        if (isset($valueNamesResponse['lang'])) {
            $response[$valueNamesResponse['lang']] = $valueNamesResponse['name'] ?? null;
        }

        return $response;
    }

    /**
     * @param $code
     * @return array
     */
    private function _getAttributeIdByCode($code)
    {
        return $this->_attributeModel->getCollection()
            ->addFieldToFilter(Attribute::ATTRIBUTE_CODE, $code)
            ->getColumnValues(Attribute::ATTRIBUTE_ID);
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response = null;
        return $this;
    }
}