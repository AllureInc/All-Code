<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
// use Magento\Framework\App\Config\ValueFactory;
use Magento\Store\Model\StoreManagerInterface;

use Plenty\Core\App\Config\Value as ConfigValue;
use Plenty\Core\App\Config\ValueFactory;
use Plenty\Core\Model\ResourceModel\Profile as ResourceProfile;

/**
 * Class Config
 * @package Plenty\Core\Model\Profile
 *
 * @method int getProfileId()
 * @method Config setProfileId(int $value)
 * @method string getSection()
 * @method Config setSection(string $value)
 * @method int getWebsite()
 * @method Config setWebsite(int $value)
 * @method int getStore()
 * @method Config setStore(int $value)
 * @method array getGroups()
 * @method Config setGroups(array $value)
 * @method string getScope()
 * @method Config setScope(string $value)
 * @method int getScopeId()
 * @method Config setScopeId(int $value)
 * @method string getPath()
 * @method Config setPath(string $value)
 * @method string getValue()
 * @method Config setValue(string $value)
 */
class Config extends AbstractModel
{
    const CACHE_TAG                 = 'plenty_core_profile_config';

    protected $_cacheTag            = 'plenty_core_profile_config';
    protected $_eventPrefix         = 'plenty_core_profile_config';

    /**
     * @var array
     */
    protected $_configData;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Config\Structure
     */
    protected $_configStructure;

    /**
     * @var ReinitableConfigInterface
     */
    protected $_appConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $_objectFactory;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var ResourceProfile\Config\Collection
     */
    protected $_configCollectionFactory;

    /**
     * @var ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var SettingChecker|mixed
     */
    private $settingChecker;

    protected function _construct()
    {
        $this->_init(ResourceProfile::class);
    }

    /**
     * Config constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ResourceProfile\Config $resource
     * @param ResourceProfile\Config\Collection $resourceCollection
     * @param ResourceProfile\Config\CollectionFactory $configCollectionFactory
     * @param ReinitableConfigInterface $config
     * @param ManagerInterface $eventManager
     * @param Config\Structure $configStructure
     * @param TransactionFactory $transactionFactory
     * @param ValueFactory $configValueFactory
     * @param StoreManagerInterface $storeManager
     * @param SettingChecker|null $settingChecker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceProfile\Config $resource,
        ResourceProfile\Config\Collection $resourceCollection,
        ResourceProfile\Config\CollectionFactory $configCollectionFactory,
        ReinitableConfigInterface $config,
        ManagerInterface $eventManager,
        Config\Structure $configStructure,
        TransactionFactory $transactionFactory,
        // Config\Loader $configLoader,
        ValueFactory $configValueFactory,
        StoreManagerInterface $storeManager,
        SettingChecker $settingChecker = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_eventManager = $eventManager;
        $this->_configStructure = $configStructure;
        $this->_transactionFactory = $transactionFactory;
        $this->_appConfig = $config;
        // $this->_configLoader = $configLoader;
        $this->_configValueFactory = $configValueFactory;
        $this->_configCollectionFactory = $configCollectionFactory->create();
        $this->_storeManager = $storeManager;
        $this->settingChecker = $settingChecker ?: ObjectManager::getInstance()->get(SettingChecker::class);
    }

    /**
     * @param $groupId
     * @param array $groupData
     * @param array $groups
     * @param $sectionPath
     * @param array $extraOldGroups
     * @param array $oldConfig
     * @param Transaction $saveTransaction
     * @param Transaction $deleteTransaction
     * @throws LocalizedException
     */
    protected function _processGroup(
        $groupId,
        array $groupData,
        array $groups,
        $sectionPath,
        array &$extraOldGroups,
        array &$oldConfig,
        Transaction $saveTransaction,
        Transaction $deleteTransaction
    ) {
        $groupPath = $sectionPath . '/' . $groupId;

        if (isset($groupData['fields'])) {
            /** @var Structure\Element\Group $group */
            $group = $this->_configStructure->getElement($groupPath);

            // set value for group field entry by fieldname
            // use extra memory
            $fieldsetData = [];
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = $fieldData['value'] ?? null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $isReadOnly = $this->settingChecker->isReadOnly(
                    $groupPath . '/' . $fieldId,
                    $this->getScope(),
                    $this->getScopeCode()
                );

                if ($isReadOnly) {
                    continue;
                }

                $field = $this->getField($sectionPath, $groupId, $fieldId);

                /** @var ConfigValue $backendModel */
                $backendModel = $field->hasBackendModel()
                    ? $this->_configValueFactory->create($field->getAttribute('backend_model'))
                    : $this->_configValueFactory->create();

                if (!isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $data = [
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'scope' => $this->getScope(),
                    'scope_id' => $this->getScopeId(),
                    'scope_code' => $this->getScopeCode(),
                    'field_config' => $field->getData(),
                    'fieldset_data' => $fieldsetData,
                ];
                $backendModel->addData($data);
                $this->_checkSingleStoreMode($field, $backendModel);

                $path = $this->getFieldPath($field, $extraOldGroups, $oldConfig);
                $backendModel->setProfileId($this->getProfileId())
                    ->setPath($path)
                    ->setValue($fieldData['value']);

                $inherit = !empty($fieldData['inherit']);
                if (isset($oldConfig[$path])) {
                    $backendModel->setConfigId($oldConfig[$path]['config_id']);

                    /**
                     * Delete config data if inherit
                     */
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                } elseif (!$inherit) {
                    $backendModel->unsConfigId();
                    $saveTransaction->addObject($backendModel);
                }
            }
        }

        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $this->_processGroup(
                    $subGroupId,
                    $subGroupData,
                    $groups,
                    $groupPath,
                    $extraOldGroups,
                    $oldConfig,
                    $saveTransaction,
                    $deleteTransaction
                );
            }
        }
    }

    /**
     * @param $path
     * @param $scope
     * @param $scopeId
     * @param bool $full
     * @return array
     */
    protected function _getConfigByPath($path, $scope, $scopeId, $full = true)
    {
        $configDataCollection = $this->_configCollectionFactory
            ->addScopeFilter($scope, $scopeId, $path, $this->getProfileId());

        $config = [];
        $configDataCollection->load();
        foreach ($configDataCollection->getItems() as $data) {
            if ($full) {
                $config[$data->getPath()] = [
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId(),
                ];
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }

    /**
     * @param bool $full
     * @return array
     */
    protected function _getConfig($full = true)
    {
        return $this->_getConfigByPath(
            $this->getSection(),
            $this->getScope(),
            $this->getScopeId(),
            $full
        );
    }

    /**
     * Set correct scope if isSingleStoreMode = true
     *
     * @param Field $fieldConfig
     * @param $dataObject
     */
    protected function _checkSingleStoreMode(
        Field $fieldConfig,
        $dataObject
    ) {
        $isSingleStoreMode = $this->_storeManager->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        if (!$fieldConfig->showInDefault()) {
            $websites = $this->_storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $dataObject->setScope('websites');
            $dataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $dataObject->setScopeCode($singleStoreWebsite->getCode());
            $dataObject->setScopeId($singleStoreWebsite->getId());
        }
    }

    /**
     * Load config data for section
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function loadData()
    {
        if (null === $this->_configData) {
            $this->initScope();
            $this->_configData = $this->_getConfig(false);
        }
        return $this->_configData;
    }

    /**
     * Extend config data with additional config data by specified path
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @param array $oldConfig Config data to extend
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = [])
    {
        $extended = $this->_getConfigByPath($path, $this->getScope(), $this->getScopeId(), $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * Add data by path section/group/field
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @throws \UnexpectedValueException
     */
    public function setDataByPath($path, $value)
    {
        $path = trim($path);
        if ($path === '') {
            throw new \UnexpectedValueException('Path must not be empty');
        }
        $pathParts = explode('/', $path);
        $keyDepth = count($pathParts);
        if ($keyDepth !== 3) {
            throw new \UnexpectedValueException(
                "Allowed depth of configuration is 3 (<section>/<group>/<field>). Your configuration depth is "
                . $keyDepth . " for path '$path'"
            );
        }
        $data = [
            'section' => $pathParts[0],
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => ['value' => $value],
                    ],
                ],
            ],
        ];

        $this->addData($data);
    }

    /**
     * Get config data value
     *
     * @param $path
     * @param null $inherit
     * @param null $configData
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfigDataValue($path, &$inherit = null, $configData = null)
    {
        $this->loadData();
        if ($configData === null) {
            $configData = $this->_configData;
        }
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->_appConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            $inherit = true;
        }

        return $data;
    }

    /**
     * Save config section
     * Require set: section, website, store and groups
     *
     * @throws \Exception
     * @return $this
     */
    public function save()
    {
        $this->initScope();

        $sectionId = $this->getSection();
        $groups = $this->getGroups();
        if (empty($groups)) {
            return $this;
        }

        $oldConfig = $this->_getConfig(true);

        /** @var Transaction $deleteTransaction */
        $deleteTransaction = $this->_transactionFactory->create();
        /** @var Transaction $saveTransaction */
        $saveTransaction = $this->_transactionFactory->create();

        $changedPaths = [];
        // Extends for old config data
        $extraOldGroups = [];

        foreach ($groups as $groupId => $groupData) {
            $this->_processGroup(
                $groupId,
                $groupData,
                $groups,
                $sectionId,
                $extraOldGroups,
                $oldConfig,
                $saveTransaction,
                $deleteTransaction
            );

            $groupChangedPaths = $this->getChangedPaths($sectionId, $groupId, $groupData, $oldConfig, $extraOldGroups);
            $changedPaths = \array_merge($changedPaths, $groupChangedPaths);
        }

        try {
            $deleteTransaction->delete();
            $saveTransaction->save();

            // re-init configuration
            $this->_appConfig->reinit();

            // website and store codes can be used in event implementation, so set them as well
            $this->_eventManager->dispatch(
                "plenty_profile_config_changed_section_{$this->getSection()}",
                [
                    'website' => $this->getWebsite(),
                    'store' => $this->getStore(),
                    'changed_paths' => $changedPaths,
                ]
            );
        } catch (\Exception $e) {
            $this->_appConfig->reinit();
            throw $e;
        }

        return $this;
    }

    /**
     * Map field name if they were cloned
     *
     * @param Group $group
     * @param string $fieldId
     * @return string
     * @throws LocalizedException
     */
    private function getOriginalFieldId(Group $group, string $fieldId): string
    {
        if ($group->shouldCloneFields()) {
            $cloneModel = $group->getCloneModel();

            /** @var Structure\Element\Field $field */
            foreach ($group->getChildren() as $field) {
                foreach ($cloneModel->getPrefixes() as $prefix) {
                    if ($prefix['field'] . $field->getId() === $fieldId) {
                        $fieldId = $field->getId();
                        break(2);
                    }
                }
            }
        }

        return $fieldId;
    }

    /**
     * @param string $sectionId
     * @param string $groupId
     * @param string $fieldId
     * @return Field
     * @throws LocalizedException
     */
    private function getField(string $sectionId, string $groupId, string $fieldId): Field
    {
        /** @var Structure\Element\Group $group */
        $group = $this->_configStructure->getElement($sectionId . '/' . $groupId);
        $fieldPath = $group->getPath() . '/' . $this->getOriginalFieldId($group, $fieldId);
        $field = $this->_configStructure->getElement($fieldPath);

        return $field;
    }

    /**
     * Get field path
     *
     * @param Field $field
     * @param array $oldConfig
     * @param array $extraOldGroups
     * @return string
     */
    private function getFieldPath(Field $field, array &$oldConfig, array &$extraOldGroups): string
    {
        $path = $field->getGroupPath() . '/' . $field->getId();

        $configPath = $field->getConfigPath();
        if ($configPath && strrpos($configPath, '/') > 0) {
            // Extend old data with specified section group
            $configGroupPath = substr($configPath, 0, strrpos($configPath, '/'));
            if (!isset($extraOldGroups[$configGroupPath])) {
                $oldConfig = $this->extendConfig($configGroupPath, true, $oldConfig);
                $extraOldGroups[$configGroupPath] = true;
            }
            $path = $configPath;
        }

        return $path;
    }

    /**
     * Check is config value changed
     *
     * @param array $oldConfig
     * @param string $path
     * @param array $fieldData
     * @return bool
     */
    private function isValueChanged(array $oldConfig, string $path, array $fieldData): bool
    {
        if (isset($oldConfig[$path]['value'])) {
            $result = !isset($fieldData['value']) || $oldConfig[$path]['value'] !== $fieldData['value'];
        } else {
            $result = empty($fieldData['inherit']);
        }

        return $result;
    }

    /**
     * Get changed paths
     *
     * @param string $sectionId
     * @param string $groupId
     * @param array $groupData
     * @param array $oldConfig
     * @param array $extraOldGroups
     * @return array
     * @throws LocalizedException
     */
    private function getChangedPaths(
        string $sectionId,
        string $groupId,
        array $groupData,
        array &$oldConfig,
        array &$extraOldGroups
    ): array {
        $changedPaths = [];

        if (isset($groupData['fields'])) {
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $field = $this->getField($sectionId, $groupId, $fieldId);
                $path = $this->getFieldPath($field, $oldConfig, $extraOldGroups);
                if ($this->isValueChanged($oldConfig, $path, $fieldData)) {
                    $changedPaths[] = $path;
                }
            }
        }

        if (isset($groupData['groups'])) {
            $subSectionId = $sectionId . '/' . $groupId;
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $subGroupChangedPaths = $this->getChangedPaths(
                    $subSectionId,
                    $subGroupId,
                    $subGroupData,
                    $oldConfig,
                    $extraOldGroups
                );
                $changedPaths = \array_merge($changedPaths, $subGroupChangedPaths);
            }
        }

        return $changedPaths;
    }

    /**
     * Get scope name and scopeId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function initScope()
    {
        if ($this->getSection() === null) {
            $this->setSection('');
        }
        if ($this->getWebsite() === null) {
            $this->setWebsite('');
        }
        if ($this->getStore() === null) {
            $this->setStore('');
        }

        if ($this->getStore()) {
            $scope = 'stores';
            $store = $this->_storeManager->getStore($this->getStore());
            $scopeId = (int)$store->getId();
            $scopeCode = $store->getCode();
        } elseif ($this->getWebsite()) {
            $scope = 'websites';
            $website = $this->_storeManager->getWebsite($this->getWebsite());
            $scopeId = (int)$website->getId();
            $scopeCode = $website->getCode();
        } else {
            $scope = 'default';
            $scopeId = 0;
            $scopeCode = '';
        }
        $this->setScope($scope);
        $this->setScopeId($scopeId);
        $this->setScopeCode($scopeCode);
    }
}