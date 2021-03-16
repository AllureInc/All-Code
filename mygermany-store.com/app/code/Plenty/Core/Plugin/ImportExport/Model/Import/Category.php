<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as CategoryAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper as ImportExportHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Event\ManagerInterface;
use Magento\CatalogImportExport\Model\Import\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

use Plenty\Core\Plugin\ImportExport\Model\Import\Enterprise\VersionFeaturesFactory;
use Plenty\Core\Plugin\ImportExport\Model\Import\Enterprise\CategoryImportVersion;
use Plenty\Core\Plugin\ImportExport\Model\ResourceModel\ImportExport\Import\Category\StorageFactory;
use Plenty\Core\Plugin\ImportExport\Model\Import\Proxy\Category\ResourceModelFactory as CategoryResourceModelFactory;

/**
 * Class Category
 * @package Plenty\Core\Plugin\ImportExport\Model\Import
 */
class Category extends Import\AbstractEntity
{

    const BUNCH_SIZE = 20;

    const SCOPE_DEFAULT = 1;
    const SCOPE_WEBSITE = 2;
    const SCOPE_STORE = 0;
    const SCOPE_NULL = -1;

    const COL_STORE = '_store';
    const COL_ROOT = '_root';
    const COL_CATEGORY = '_category';

    const ERROR_INVALID_SCOPE = 'invalidScope';
    const ERROR_INVALID_WEBSITE = 'invalidWebsite';
    const ERROR_INVALID_STORE = 'invalidStore';
    const ERROR_INVALID_ROOT = 'invalidRoot';
    const ERROR_CATEGORY_IS_EMPTY = 'categoryIsEmpty';
    const ERROR_PARENT_NOT_FOUND = 'parentNotFound';
    const ERROR_NO_DEFAULT_ROW = 'noDefaultRow';
    const ERROR_DUPLICATE_CATEGORY = 'duplicateCategory';
    const ERROR_DUPLICATE_SCOPE = 'duplicateScope';
    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';
    const ERROR_VALUE_IS_REQUIRED = 'valueIsRequired';
    const ERROR_CATEGORY_NOT_FOUND_FOR_DELETE = 'categoryNotFoundToDelete';

    const XML_PATH_CATEGORY_PATH_SEPERATOR = '/';
    const XML_PATH_VALIDATION_STRATEGY = 'validation-stop-on-errors';
    /**
     * @var string
     */
    protected $masterAttributeCode = self::COL_CATEGORY;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $categoriesWithRoots = [];

    /**
     * @var
     */
    protected $entityTable;

    /**
     * @var array
     */
    protected $indexValueAttributes = [
        'default_sort_by',
        CategoryModel::KEY_AVAILABLE_SORT_BY,
        CategoryModel::KEY_IS_ACTIVE,
        CategoryModel::KEY_INCLUDE_IN_MENU,
        'is_anchor'
    ];

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::ERROR_INVALID_SCOPE => 'Invalid value in Scope column',
        self::ERROR_INVALID_WEBSITE => 'Invalid value in Website column (website does not exists?)',
        self::ERROR_INVALID_STORE => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_INVALID_ROOT => 'Root category doesn\'t exist',
        self::ERROR_CATEGORY_IS_EMPTY => 'Category is empty',
        self::ERROR_PARENT_NOT_FOUND => 'Parent Category is not found, add parent first',
        self::ERROR_NO_DEFAULT_ROW => 'Default values row does not exists',
        self::ERROR_DUPLICATE_CATEGORY => 'Duplicate category',
        self::ERROR_DUPLICATE_SCOPE => 'Duplicate scope',
        self::ERROR_ROW_IS_ORPHAN => 'Orphan rows that will be skipped due default row errors',
        self::ERROR_VALUE_IS_REQUIRED => 'Required attribute \'%s\' has an empty value',
        self::ERROR_CATEGORY_NOT_FOUND_FOR_DELETE => 'Category not found for delete'
    ];

    /**
     * @var array
     */
    protected $imagesArrayKeys = [
        'thumbnail', 'image'
    ];

    /**
     * @var array
     */
    protected $newCategory = [];

    /**
     * @var array
     */
    protected $_specialAttributes = [
        self::COL_STORE, self::COL_ROOT, self::COL_CATEGORY
    ];

    /**
     * @var array
     */
    protected $_permanentAttributes = [
        self::COL_ROOT, self::COL_CATEGORY
    ];

    /**
     * @var int
     */
    protected $errorsLimit;

    /**
     * @var array
     */
    protected $invalidRows = [];

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $storeCodeToId = [];

    /**
     * Store ID to its website stores IDs.
     *
     * @var array
     */
    protected $storeIdToWebsiteStoreIds = [];

    /**
     * Website code-to-ID
     *
     * @var array
     */
    protected $websiteCodeToId = [];

    /**
     * Website code to store code-to-ID pairs which it consists.
     *
     * @var array
     */
    protected $websiteCodeToStoreIds = [];

    /**
     * @var CategoryImportVersion
     */
    protected $categoryImportVersionFeature;

    /**
     * @var
     */
    protected $fileUploader;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var bool
     */
    protected $unsetEmptyFields = false;

    /**
     * @var bool
     */
    protected $symbolEmptyFields = false;

    /**
     * @var bool
     */
    protected $symbolIgnoreFields = false;

    /**
     * @var int
     */
    protected $defaultAttributeSetId = 0;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StorageFactory
     */
    protected $storageFactory;

    /**
     * @var CategoryModel
     */
    protected $defaultCategory;

    /**
     * @var CategoryAttributeCollection
     */
    protected $attributeCollection;

    /**
     * @var CategoryCollection
     */
    protected $categoryCollection;

    /**
     * @var ImportExportHelper
     */
    protected $resourceHelper;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var CategoryResourceModelFactory
     */
    protected $resourceFactory;

    /**
     * @var string|null
     */
    protected $entityTypeId;

    /**
     * @var VersionFeaturesFactory
     */
    private $versionFeatures;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * Category constructor.
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param ImportExportHelper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param StoreManagerInterface $storeManager
     * @param StorageFactory $storageFactory
     * @param CategoryModel $defaultCategory
     * @param CategoryAttributeCollection $attributeCollection
     * @param CategoryCollection $categoryCollection
     * @param EavConfig $eavConfig
     * @param ManagerInterface $eventManager
     * @param UploaderFactory $imageUploaderFactory
     * @param Filesystem $filesystem
     * @param VersionFeaturesFactory $versionFeatures
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param CategoryResourceModelFactory $resourceFactory
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UrlPersistInterface $urlPersist
     * @param array $data
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        ImportExportHelper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface $storeManager,
        StorageFactory $storageFactory,
        CategoryModel $defaultCategory,
        CategoryAttributeCollection $attributeCollection,
        CategoryCollection $categoryCollection,
        EavConfig $eavConfig,
        ManagerInterface $eventManager,
        UploaderFactory $imageUploaderFactory,
        Filesystem $filesystem,
        VersionFeaturesFactory $versionFeatures,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        CategoryResourceModelFactory $resourceFactory,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        CategoryRepositoryInterface $categoryRepository,
        UrlPersistInterface $urlPersist,
        array $data = []
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );

        $this->resourceHelper = $resourceHelper;
        $this->storeManager = $storeManager;
        $this->storageFactory = $storageFactory;
        $this->defaultCategory = $defaultCategory;
        $this->attributeCollection = $attributeCollection;
        $this->categoryCollection = $categoryCollection;
        $this->eventManager = $eventManager;
        $this->uploaderFactory = $imageUploaderFactory;
        $this->versionFeatures = $versionFeatures;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->objectRelationProcessor = $objectRelationProcessor;
        $this->transactionManager = $transactionManager;
        $this->resourceFactory = $resourceFactory;
        $entityType = $eavConfig->getEntityType($this->getEntityTypeCode());
        $this->entityTypeId = $entityType->getEntityTypeId();

        foreach ($this->messageTemplates as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }

        $this->initWebsites()
            ->initStores()
            ->initCategories()
            ->initAttributes()
            ->initAttributeSetId();

        $this->entityTable = $this->defaultCategory->getResource()->getEntityTable();
        $this->categoryImportVersionFeature = $this->versionFeatures->create('CategoryImportVersion');
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return CategoryModel::ENTITY;
    }

    /**
     * @return $this
     */
    protected function initAttributeSetId()
    {
        $this->defaultAttributeSetId = $this->defaultCategory->getDefaultAttributeSetId();
        return $this;
    }

    /**
     * @return $this
     */
    protected function initAttributes()
    {
        foreach ($this->attributeCollection as $attribute) {
            $this->attributes[$attribute->getAttributeCode()] = [
                'id' => $attribute->getId(),
                'is_required' => $attribute->getIsRequired(),
                'is_static' => $attribute->isStatic(),
                'rules' => $attribute->getValidateRules() ? unserialize($attribute->getValidateRules()) : null,
                'type' => Import::getAttributeType($attribute),
                'options' => $this->getAttributeOptions($attribute),
                'attribute' => $attribute
            ];
        }

        return $this;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     */
    public function getAttributeOptions(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute)
    {
        $options = [];

        if ($attribute->usesSource()) {
            $index = in_array($attribute->getAttributeCode(), $this->indexValueAttributes) ? 'value' : 'label';

            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

            try {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : [$option] as $innerOption) {
                        if (strlen($innerOption['value'])) {
                            // skip ' -- Please Select -- ' option
                            $options[strtolower($innerOption['value'])] = (string)$innerOption[$index];
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return $options;
    }

    /**
     * @return $this
     */
    protected function initCategories()
    {
        $collection = $this->getCollection();

        /** @var CategoryModel $category */
        foreach ($collection as $category) {
            $structure = explode('/', $category->getData(CategoryModel::KEY_PATH));
            $pathSize = count($structure);

            if ($pathSize > 1) {
                $path = [];
                for ($i = 1; $i < $pathSize; $i++) {
                    /** @var CategoryModel $c */
                    $c = $collection->getItemById($structure[$i]);
                    $path[] = $c->getData(CategoryModel::KEY_NAME);
                }

                $rootCategoryName = array_shift($path);
                if (!isset($this->categoriesWithRoots[$rootCategoryName])) {
                    $this->categoriesWithRoots[$rootCategoryName] = [];
                }

                $index = $this->implodeEscaped($path);
                $this->categoriesWithRoots[$rootCategoryName][$index] = [
                    'entity_id' => $category->getId(),
                    CategoryModel::KEY_PATH => $category->getData(CategoryModel::KEY_PATH),
                    CategoryModel::KEY_LEVEL => $category->getData(CategoryModel::KEY_LEVEL),
                    CategoryModel::KEY_POSITION => $category->getData(CategoryModel::KEY_POSITION)
                ];

                //allow importing by ids.
                if (!isset($this->categoriesWithRoots[$structure[1]])) {
                    $this->categoriesWithRoots[$structure[1]] = [];
                }

                $this->categoriesWithRoots[$structure[1]][$category->getId()] =
                    $this->categoriesWithRoots[$rootCategoryName][$index];
            }
        }

        return $this;
    }

    /**
     * @return CategoryCollection
     */
    protected function getCollection()
    {
        return $this->categoryCollection->setStoreId(0)->addNameToResult();
    }

    /**
     * @param mixed $glue
     * @param array $array
     * @return string
     */
    protected function implodeEscaped(array $array, $glue = self::XML_PATH_CATEGORY_PATH_SEPERATOR)
    {
        $newArray = [];
        foreach ($array as $value) {
            $newArray[] = str_replace($glue, '\\' . $glue, $value);
        }
        return implode(self::XML_PATH_CATEGORY_PATH_SEPERATOR, $newArray);
    }

    /**
     * @param bool $withDefault
     * @return $this
     */
    protected function initStores($withDefault = false)
    {
        /** @var $store \Magento\Store\Model\Store */
        foreach ($this->storeManager->getStores($withDefault) as $store) {
            $this->storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }

    /**
     * @param bool $withDefault
     * @return $this
     */
    protected function initWebsites($withDefault = false)
    {
        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->storeManager->getWebsites($withDefault) as $website) {
            $this->websiteCodeToId[$website->getCode()] = $website->getId();
        }
        return $this;
    }

    /**
     * @param boolean $value
     * @return $this
     */
    public function setUnsetEmptyFields($value)
    {
        $this->unsetEmptyFields = (boolean)$value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSymbolEmptyFields($value)
    {
        $this->symbolEmptyFields = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSymbolIgnoreFields($value)
    {
        $this->symbolIgnoreFields = $value;
        return $this;
    }

    /**
     * @param $limit
     */
    public function setErrorLimit($limit)
    {
        if ($limit) {
            $this->errorsLimit = $limit;
        } else {
            $this->errorsLimit = 100;
        }
    }

    /**
     * @return int
     */
    public function getErrorLimit()
    {
        return (int)$this->errorsLimit;
    }

    /**
     * @return array
     */
    public function getCategoriesWithRoots()
    {
        return $this->categoriesWithRoots;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|mixed
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return array|null
     */
    public function getNextBunch()
    {
        return $this->_dataSourceModel->getNextBunch();
    }

    /**
     * @return array
     */
    public function getWebsiteCodes()
    {
        return $this->websiteCodeToId;
    }

    /**
     * @return array
     */
    public function getAffectedEntityIds()
    {
        $categoryIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->filterRowData($rowData);
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                if (!isset($this->newCategory[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]]['entity_id'])) {
                    continue;
                }
                $categoryIds[] = $this->newCategory[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]]['entity_id'];
            }
        }
        return $categoryIds;
    }

    /**
     * @param $rowData
     */
    protected function filterRowData(&$rowData)
    {
        if ($this->unsetEmptyFields || $this->symbolEmptyFields || $this->symbolIgnoreFields) {
            foreach ($rowData as $key => $fieldValue) {
                if ($this->unsetEmptyFields && !strlen($fieldValue)) {
                    unset($rowData[$key]);
                } else if ($this->symbolEmptyFields && trim($fieldValue) == $this->symbolEmptyFields) {
                    $rowData[$key] = NULL;
                } else if ($this->symbolIgnoreFields && trim($fieldValue) == $this->symbolIgnoreFields) {
                    unset($rowData[$key]);
                }
            }
        }
    }

    /**
     * @param $source
     * @return $this
     */
    public function setArraySource($source)
    {
        $this->_source = $source;
        $this->_dataValidated = false;

        return $this;
    }

    /**
     * @param $behavior
     */
    public function setBehavior($behavior)
    {
        $this->_parameters['behavior'] = $behavior;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function reindexImportedCategories()
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->indexDeleteEvents();
                break;
            case Import::BEHAVIOR_REPLACE:
            case Import::BEHAVIOR_APPEND:
            case Import::BEHAVIOR_ADD_UPDATE:

                // $this->reindexUpdatedCategories();
                break;
            default:
                throw new \Exception('Unsupported Mode!');
                break;
        }
        return $this;
    }

    /**
     * @return Category
     * @throws \Exception
     */
    protected function indexDeleteEvents()
    {
        // return $this->reindexUpdatedCategories();
    }

    /**
     * @param $categoryId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    protected function reindexUpdatedCategories($categoryId)
    {
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->defaultCategory->load($categoryId);
        //$categoryName = $category->getName();
        foreach ($category->getStoreIds() as $storeId) {
            if ($storeId == 0) {
                continue;
            }

            $category = $this->categoryRepository->get($categoryId, $storeId);

            $urlRewrites = $this->categoryUrlRewriteGenerator->generate($category, true);
            $this->urlPersist->replace($urlRewrites);
        }
        return $this;
    }

    public function updateChildrenCount()
    {
        /** @todo impolement this */
    }

    /**
     * @param string $root
     * @param string $category
     *
     * @return array|false
     */
    public function getEntityByCategory($root, $category)
    {
        if (isset($this->categoriesWithRoots[$root][$category])) {
            return $this->categoriesWithRoots[$root][$category];
        }

        if (isset($this->newCategory[$root][$category])) {
            return $this->newCategory[$root][$category];
        }

        return false;
    }

    /**
     * @return $this|bool
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteCategories();
        } else {
            $this->saveCategories();
        }

        // $this->reindexImportedCategories();
        // $this->eventManager->dispatch('catalog_category_import_bunch_save_after', ['adapter' => $this, 'bunch' => $this->getProcessedCategoryIds()]);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function deleteCategories()
    {
        $categoryEntityTable = $this->resourceFactory->create()->getEntityTable();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idToDelete = [];

            foreach ($bunch as $rowNum => $rowData) {
                $this->filterRowData($rowData);
                if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $idToDelete[] = $this->categoriesWithRoots[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]]['entity_id'];
                }
            }
            if ($idToDelete) {
                $this->countItemsDeleted += count($idToDelete);
                $this->transactionManager->start($this->_connection);
                try {
                    $this->objectRelationProcessor->delete(
                        $this->transactionManager,
                        $this->_connection,
                        $categoryEntityTable,
                        $this->_connection->quoteInto('entity_id IN (?)', $idToDelete),
                        ['entity_id' => $idToDelete]
                    );
                    $this->transactionManager->commit();
                } catch (\Exception $e) {
                    $this->transactionManager->rollBack();
                    throw $e;
                }
                $this->eventManager->dispatch('catalog_category_import_bunch_delete_after', ['adapter' => $this, 'bunch' => $bunch]);
            }
        }

        return $this;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        static $root = null;
        static $category = null;

        if (isset($rowData['fsi_line_number'])) {
            $rowNum = $rowData['fsi_line_number'];
        }
        $this->filterRowData($rowData);

        if (isset($this->_validatedRows[$rowNum])) {
            return !isset($this->invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (isset($rowData[self::COL_ROOT])
            && isset($rowData[self::COL_CATEGORY])
            && isset($this->newCategory[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]])
        ) {
            if (!$this->getIgnoreDuplicates()) {
                $this->addRowError(self::ERROR_DUPLICATE_CATEGORY, $rowNum);
            }

            return false;
        }
        $rowScope = $this->getRowScope($rowData);

        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope
                && !isset($this->categoriesWithRoots[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]])
            ) {
                $this->addRowError(self::ERROR_CATEGORY_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }

        if (self::SCOPE_DEFAULT == $rowScope) { // category is specified, row is SCOPE_DEFAULT, new category block begins
            $rowData[CategoryModel::KEY_NAME] = $this->getCategoryName($rowData);

            $this->_processedEntitiesCount++;

            $root = $rowData[self::COL_ROOT];
            $category = $rowData[self::COL_CATEGORY];

            if (!isset($this->categoriesWithRoots[$root])) {
                $this->addRowError(self::ERROR_INVALID_ROOT, $rowNum);
                return false;
            }

            if ($this->getParentCategory($rowData) === false) {

                $this->addRowError(self::ERROR_PARENT_NOT_FOUND, $rowNum);
                return false;
            }

            if (isset($this->categoriesWithRoots[$root][$category])) {

            } else {
                if (!isset($this->newCategory[$root][$category])) {
                    $this->newCategory[$root][$category] = ['entity_id' => null];
                }
                if (isset($this->invalidRows[$rowNum])) {
                    $category = false;
                }
            }

            foreach ($this->attributes as $attrCode => $attrParams) {
                if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                    $this->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
                } elseif ($attrParams['is_required'] && !isset($this->categoriesWithRoots[$root][$category])) {
                    $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNum, $attrCode);
                }
            }

        } else {
            if (null === $category) {
                $this->addRowError(self::ERROR_CATEGORY_IS_EMPTY, $rowNum);
            } elseif (false === $category) {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            } elseif (self::SCOPE_STORE == $rowScope && !isset($this->storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }

        if (isset($this->invalidRows[$rowNum])) {
            $category = false;
        }

        return !isset($this->invalidRows[$rowNum]);
    }

    /**
     * @return bool
     */
    public function getIgnoreDuplicates()
    {
        return (boolean)$this->_parameters['ignore_duplicates'];
    }

    /**
     *
     */
    public function setIgnoreDuplicates()
    {
        $this->_parameters['ignore_duplicates'] = true;
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (isset($rowData[self::COL_CATEGORY]) && strlen(trim($rowData[self::COL_CATEGORY]))) {
            return self::SCOPE_DEFAULT;
        } elseif (empty($rowData[self::COL_STORE])) {
            return self::SCOPE_NULL;
        } else {
            return self::SCOPE_STORE;
        }
    }

    /**
     * @param array $rowData
     * @return string
     */
    protected function getCategoryName($rowData)
    {
        if (isset($rowData[CategoryModel::KEY_NAME]) && strlen($rowData[CategoryModel::KEY_NAME])) {
            return $rowData[CategoryModel::KEY_NAME];
        }

        $categoryParts = $this->explodeEscaped($rowData[self::COL_CATEGORY]);
        return end($categoryParts);
    }

    /**
     * @param string $delimiter
     * @param string $string
     * @return array
     */
    protected function explodeEscaped($string, $delimiter = '/')
    {
        $exploded = explode($delimiter, $string);
        $fixed = [];
        for ($k = 0, $l = count($exploded); $k < $l; ++$k) {
            $eIdx = strlen($exploded[$k]) - 1;
            if ($eIdx >= 0 && $exploded[$k][$eIdx] == '\\') {
                if ($k + 1 >= $l) {
                    $fixed[] = trim($exploded[$k]);
                    break;
                }
                $exploded[$k][$eIdx] = $delimiter;
                $exploded[$k] .= $exploded[$k + 1];
                array_splice($exploded, $k + 1, 1);
                --$l;
                --$k;
            } else $fixed[] = trim($exploded[$k]);
        }
        return $fixed;
    }

    /**
     * @param $rowData
     * @return bool|mixed
     */
    protected function getParentCategory($rowData)
    {
        if ($rowData[self::COL_CATEGORY] == $this->getCategoryName($rowData)) {
            // if _category eq. name then we don't have parents
            $parent = false;
        } else {
            $categoryParts = $this->explodeEscaped($rowData[self::COL_CATEGORY]);
            array_pop($categoryParts);
            $parent = $this->implodeEscaped($categoryParts);
        }

        if ($parent) {
            if (isset($this->categoriesWithRoots[$rowData[self::COL_ROOT]][$parent])) {
                return $this->categoriesWithRoots[$rowData[self::COL_ROOT]][$parent];
            } elseif (isset($this->newCategory[$rowData[self::COL_ROOT]][$parent])) {
                return $this->newCategory[$rowData[self::COL_ROOT]][$parent];
            } else {
                return false;
            }
        } elseif (isset($this->categoriesWithRoots[$rowData[self::COL_ROOT]])) {
            return reset($this->categoriesWithRoots[$rowData[self::COL_ROOT]]);
        } else {
            return false;
        }
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function saveCategories()
    {
        $nextEntityId = $this->resourceHelper->getNextAutoincrement($this->entityTable);
        static $entityId;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $uploadedGalleryFiles = [];

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                $rowData = $this->_prepareRowForDb($rowData);
                $this->filterRowData($rowData);

                if (self::SCOPE_DEFAULT == $rowScope) {
                    $parentCategory = $this->getParentCategory($rowData);

                    $time = !empty($rowData[CategoryModel::KEY_CREATED_AT]) ? strtotime($rowData[CategoryModel::KEY_CREATED_AT]) : null;

                    $entityRow = [
                        CategoryModel::KEY_PARENT_ID => $parentCategory['entity_id'],
                        CategoryModel::KEY_LEVEL => $parentCategory[CategoryModel::KEY_LEVEL] + 1,
                        CategoryModel::KEY_CREATED_AT => (new \DateTime($time))->format(DateTime::DATETIME_PHP_FORMAT),
                        CategoryModel::KEY_UPDATED_AT => "now()",
                        CategoryModel::KEY_POSITION => $rowData[CategoryModel::KEY_POSITION]
                    ];

                    if (isset($this->categoriesWithRoots[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]])) { //edit
                        $entityId = $this->categoriesWithRoots[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]]['entity_id'];
                        $entityRow['entity_id'] = $entityId;
                        $entityRow[CategoryModel::KEY_PATH] = $parentCategory[CategoryModel::KEY_PATH] . '/' . $entityId;
                        $entityRowsUp[] = $entityRow;
                        $rowData['entity_id'] = $entityId;

                    } else {
                        $entityId = $nextEntityId++;
                        $entityRow['entity_id'] = $entityId;
                        $entityRow[CategoryModel::KEY_PATH] = $parentCategory[CategoryModel::KEY_PATH] . '/' . $entityId;
                        $entityRow['attribute_set_id'] = $this->defaultAttributeSetId;
                        $entityRowsIn[] = $entityRow;

                        $this->newCategory[$rowData[self::COL_ROOT]][$rowData[self::COL_CATEGORY]] = [
                            'entity_id' => $entityId,
                            CategoryModel::KEY_PATH => $entityRow[CategoryModel::KEY_PATH],
                            CategoryModel::KEY_LEVEL => $entityRow[CategoryModel::KEY_LEVEL]
                        ];
                    }
                }

                foreach ($this->imagesArrayKeys as $imageCol) {
                    if (!empty($rowData[$imageCol])) { // 5. Media gallery phase
                        if (!array_key_exists($rowData[$imageCol], $uploadedGalleryFiles)) {
                            $uploadedGalleryFiles[$rowData[$imageCol]] = $this->uploadMediaFiles($rowData[$imageCol]);
                        }
                        $rowData[$imageCol] = $uploadedGalleryFiles[$rowData[$imageCol]];
                    }
                }

                $rowStore = self::SCOPE_STORE == $rowScope ? $this->storeCodeToId[$rowData[self::COL_STORE]] : 0;

                /** @var $category \Magento\Catalog\Model\Category */
                $category = $this->defaultCategory->setData($rowData);

                foreach (array_intersect_key($rowData, $this->attributes) as $attrCode => $attrValue) {
                    if (!$this->attributes[$attrCode]['is_static']) {

                        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
                        $attribute = $this->attributes[$attrCode]['attribute'];

                        if ('multiselect' != $attribute->getFrontendInput()
                            && self::SCOPE_NULL == $rowScope
                        ) {
                            continue;
                        }

                        $attrId = $attribute->getAttributeId();
                        $backModel = $attribute->getBackendModel();
                        $attrTable = $attribute->getBackend()->getTable();
                        $attrParams = $this->attributes[$attrCode];
                        $storeIds = [0];

                        if ('select' == $attrParams['type']) {
                            if (isset($attrParams['options'][strtolower($attrValue)])) {
                                $attrValue = $attrParams['options'][strtolower($attrValue)];
                            }
                        } elseif ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                            $attrValue = (new \DateTime(strtotime($attrValue)))->format(DateTime::DATETIME_PHP_FORMAT);
                        } elseif ($backModel && 'available_sort_by' != $attrCode) {
                            $attribute->getBackend()->beforeSave($category);
                            $attrValue = $category->getData($attribute->getAttributeCode());
                        }

                        if (self::SCOPE_STORE == $rowScope) {
                            if (self::SCOPE_WEBSITE == $attribute->getData('is_global')) {
                                // check website defaults already set
                                if (!isset($attributes[$attrTable][$entityId][$attrId][$rowStore])) {
                                    $storeIds = $this->storeIdToWebsiteStoreIds[$rowStore];
                                }
                            } elseif (self::SCOPE_STORE == $attribute->getData('is_global')) {
                                $storeIds = [$rowStore];
                            }
                        }

                        foreach ($storeIds as $storeId) {
                            if ('multiselect' == $attribute->getFrontendInput()) {
                                if (!isset($attributes[$attrTable][$entityId][$attrId][$storeId])) {
                                    $attributes[$attrTable][$entityId][$attrId][$storeId] = '';
                                } else {
                                    $attributes[$attrTable][$entityId][$attrId][$storeId] .= ',';
                                }
                                $attributes[$attrTable][$entityId][$attrId][$storeId] .= $attrValue;
                            } else {
                                $attributes[$attrTable][$entityId][$attrId][$storeId] = $attrValue;
                            }
                        }

                        $attribute->setBackendModel($backModel); // restore 'backend_model' to avoid 'default' setting
                    }
                }
            }

            $this->saveCategoryEntity($entityRowsIn, $entityRowsUp);
            $this->saveCategoryAttributes($attributes);
        }
        return $this;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData = parent::_prepareRowForDb($rowData);
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            return $rowData;
        }

        if (self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
            $rowData[CategoryModel::KEY_NAME] = $this->getCategoryName($rowData);
            if (!isset($rowData[CategoryModel::KEY_POSITION])) {
                $rowData[CategoryModel::KEY_POSITION] = 10000;
            }
        }

        return $rowData;
    }

    /**
     * @param $fileName
     * @return string
     */
    protected function uploadMediaFiles($fileName)
    {
        try {
            $res = $this->getUploader()->move($fileName);
            return $res['file'];
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getUploader()
    {
        if (is_null($this->fileUploader)) {
            $this->fileUploader = $this->uploaderFactory->create();

            $this->fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];
            $DS = DIRECTORY_SEPARATOR;
            $tmpPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath('import');

            if (!$this->fileUploader->setTmpDir($tmpPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }

            $destinationDir = "catalog/category";
            $destinationPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath($destinationDir);
            $this->mediaDirectory->create($destinationPath);

            if (!$this->fileUploader->setDestDir($destinationPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }

        return $this->fileUploader;
    }

    /**
     * @param array $entityRowsIn
     * @param array $entityRowsUp
     * @return $this
     * @throws \Exception
     */
    protected function saveCategoryEntity(array $entityRowsIn, array $entityRowsUp)
    {
        if ($entityRowsIn) {
            if ($this->categoryImportVersionFeature) {
                $entityRowsIn = $this->categoryImportVersionFeature->processCategory($entityRowsIn);
            }

            $this->_connection->insertMultiple($this->entityTable, $entityRowsIn);
        }

        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $this->entityTable,
                $entityRowsUp,
                [CategoryModel::KEY_PARENT_ID, CategoryModel::KEY_PATH, CategoryModel::KEY_POSITION, CategoryModel::KEY_LEVEL, 'children_count']
            );
        }

        return $this;
    }

    /**
     * @param array $attributesData
     * @return $this
     * @throws \Exception
     */
    protected function saveCategoryAttributes(array $attributesData)
    {
        $entityFieldName = 'entity_id';
        if ($this->categoryImportVersionFeature) {
            $entityFieldName = $this->categoryImportVersionFeature->getEntityFieldName();
        }

        $entityIds = array();

        foreach ($attributesData as $tableName => $data) {
            $tableData = [];

            foreach ($data as $entityId => $attributes) {
                $entityIds[] = $entityId;

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = [
                            $entityFieldName => $entityId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue
                        ];
                    }
                }
            }

            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }

        $entityIds = array_unique($entityIds);
        foreach ($entityIds as $entityId) {
            $this->reindexUpdatedCategories($entityId);
        }


        return $this;
    }

    /**
     * @param array $rowData
     * @return bool
     */
    protected function isRowScopeDefault(array $rowData)
    {
        return strlen(trim($rowData[self::COL_CATEGORY])) ? true : false;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getProcessedCategoryIds()
    {
        $categoryIds = [];
        $source = $this->getSource();

        $source->rewind();
        while ($source->valid()) {
            $current = $source->current();
            if (isset($this->newCategory[$current[self::COL_ROOT]][$current[self::COL_CATEGORY]])) {
                $categoryIds[] = $this->newCategory[$current[self::COL_ROOT]][$current[self::COL_CATEGORY]];
            } elseif (isset($this->categoriesWithRoots[$current[self::COL_ROOT]][$current[self::COL_CATEGORY]])) {
                $categoryIds[] = $this->categoriesWithRoots[$current[self::COL_ROOT]][$current[self::COL_CATEGORY]];
            }

            $source->next();
        }

        return $categoryIds;
    }

    /**
     * @return Import\AbstractEntity
     * @throws LocalizedException
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->getSource();
        $source->rewind();
        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                $this->_processedRowsCount++;
                $source->next();
                continue;
            }

            $rowData = $this->customFieldsMapping($rowData);

            $this->validateRow($rowData, $source->key());
            $source->next();
        }

        return parent::_saveValidatedBunches();
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function customFieldsMapping($rowData)
    {
        return $rowData;
    }
}
