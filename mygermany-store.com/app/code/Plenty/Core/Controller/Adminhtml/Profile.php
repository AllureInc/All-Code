<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;

use Plenty\Core\Model\Profile as ProfileModel;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Api\ProfileRepositoryInterface;

/**
 * Class Profile
 * @package Plenty\Core\Controller\Adminhtml
 */
abstract class Profile extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE        = 'Plenty_Core::plenty_core_profile';

    /**
     * @var string
     */
    const PROFILE_BASE_URL      = 'plenty_core/profile/';

    /**
     * @var ProfileModel\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $_profileRepository;

    /**
     * @var ProfileModel\HistoryFactory
     */
    protected $_profileHistoryFactory;

    /**
     * @var Profile
     */
    protected $_currentProfile;

    /**
     * @var ProfileModel\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var ProfileModel\Type
     */
    protected $_profileEntityType;

    /**
     * @var
     */
    protected $_formFactory;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * Profile constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StringUtils $string
     * @param ProfileModel\Config\Structure $configStructure
     * @param ProfileModel\ConfigFactory $configFactory
     * @param ProfileModel\Type $profileEntityType
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileModel\HistoryFactory $profileHistoryFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        DataPersistorInterface $dataPersistor,
        StringUtils $string,
        ProfileModel\Config\Structure $configStructure,
        ProfileModel\ConfigFactory $configFactory,
        ProfileModel\Type $profileEntityType,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        ProfileModel\HistoryFactory $profileHistoryFactory,
        DateTime $dateTime
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->dataPersistor = $dataPersistor;
        $this->string = $string;
        $this->_configStructure = $configStructure;
        $this->_configFactory = $configFactory;
        $this->_profileEntityType = $profileEntityType;
        $this->_profileFactory = $profileFactory;
        $this->_profileRepository = $profileRepository;
        $this->_profileHistoryFactory = $profileHistoryFactory;
        $this->_dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|ProfileInterface|Profile|ProfileModel
     */
    protected function _initCurrentProfile()
    {
        $this->_currentProfile = null;
        $model = $this->_profileFactory->create();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->_profileRepository->getById($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_currentProfile = $model;
        $this->_coreRegistry->register(Profile\RegistryConstants::CURRENT_PROFILE, $this->_currentProfile);

        return $this->_currentProfile;
    }

    /**
     * Register current profile ID
     *
     * @return mixed
     */
    protected function initCurrentProfileId()
    {
        if ($profileId = $this->getRequest()->getParam('id')) {
            $this->_coreRegistry->register('current_profile_id', (int) $profileId);
        }
        return $profileId;
    }

    /**
     * Prepare profile default title
     *
     * @param Page $resultPage
     * @return void
     */
    protected function prepareDefaultProfileTitle(Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
    }

    /**
     * Add errors messages to session.
     *
     * @param $messages
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array) $messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * @param callable $singleAction
     * @param $profileIds
     * @return int
     */
    protected function actUponMultipleProfiles(callable $singleAction, $profileIds)
    {
        if (!is_array($profileIds)) {
            $this->messageManager->addErrorMessage(__('Please select profile(s).'));
            return 0;
        }
        $profileUpdated = 0;
        foreach ($profileIds as $profileId) {
            try {
                $singleAction($profileId);
                $profileUpdated++;
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        return $profileUpdated;
    }

    /**
     * @return array
     */
    protected function _extractProfileData()
    {
        $profileData = [
            ProfileModel::IS_ACTIVE => $this->getRequest()->getParam(ProfileModel::IS_ACTIVE)
        ];
        if ($name = $this->getRequest()->getParam(ProfileModel::NAME)) {
            $profileData[ProfileModel::NAME] = $name;
        }
        if (!$this->getRequest()->getParam('id')
            && $entity = $this->getRequest()->getParam(ProfileModel::ENTITY)
        ) {
            $profileData[ProfileModel::ENTITY] = $entity;
        }
        if (!$this->getRequest()->getParam('id')
            && $adaptor = $this->getRequest()->getParam(ProfileModel::ADAPTOR)
        ) {
            $profileData[ProfileModel::ADAPTOR] = $adaptor;
        }
        if ($crontab = $this->getRequest()->getParam(ProfileModel::CRONTAB)) {
            $profileData[ProfileModel::CRONTAB] = $crontab;
        }
        return $profileData;
    }

    /**
     * Process nested groups
     *
     * @param mixed $group
     * @return array
     */
    protected function _processNestedGroups($group)
    {
        $data = [];
        if (isset($group['fields']) && is_array($group['fields'])) {
            foreach ($group['fields'] as $fieldName => $field) {
                if (empty($field['value'])) {
                    continue;
                }
                $data['fields'][$fieldName] = ['value' => $field['value']];
            }
        }

        if (isset($group['groups']) && is_array($group['groups'])) {
            foreach ($group['groups'] as $groupName => $groupData) {
                $nestedGroup = $this->_processNestedGroups($groupData);
                if (empty($nestedGroup)) {
                    continue;
                }
                $data['groups'][$groupName] = $nestedGroup;
            }
        }

        return $data;
    }

    /**
     * Advanced save procedure
     *
     * @return void
     */
    protected function _saveAdvanced()
    {
        $this->_cache->clean();
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _save()
    {
        $profile = $this->_saveProfile();
        $this->_saveProfileConfig();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('back')
            || !$this->getRequest()->getParam('id')
        ) {
            $profileUrl = $this->_profileEntityType->getRouter($profile);
            return $resultRedirect->setPath("{$profileUrl}/edit",
                [
                    'id' => $profile->getId(),
                    'section' => "{$profile->getEntity()}_{$profile->getAdaptor()}",
                    'website' => $this->getRequest()->getParam('website')
                        ? $this->getRequest()->getParam('website')
                        : null,
                    'store' => $this->getRequest()->getParam('store')
                        ? $this->getRequest()->getParam('store')
                        : null,
                    'active_tab' => $this->getRequest()->getParam('active_tab')
                        ? $this->getRequest()->getParam('active_tab')
                        : null
                ]
            );
        }
        return $resultRedirect->setPath(self::PROFILE_BASE_URL);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|mixed|\Plenty\Core\Model\Profile
     */
    protected function _saveProfile()
    {
        if (!$profileData = $this->_extractProfileData()) {
            $this->messageManager->addErrorMessage(__('Could not retrieve profile data.'));
        }

        $profile = $this->_initCurrentProfile();

        if ($this->getRequest()->getParam('id')) {
            $profile->addData($profileData);
        } else {
            $profile->setData($profileData);
        }

        try {
            /** @var \Plenty\Core\Model\Profile $profile */
            $profile = $this->_profileRepository->save($profile);
            $this->messageManager->addSuccessMessage(__('Profile has been saved'));
            $this->dataPersistor->clear('profile');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('profile', $profileData);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the profile.'));
            $this->dataPersistor->set('profile', $profileData);
        }

        return $profile;
    }

    /**
     * @return bool|ProfileModel\Config
     */
    protected function _saveProfileConfig()
    {
        if (!$this->getRequest()->getParam('groups')
            || !$profileId = $this->getRequest()->getParam('id')
        ) {
            return false;
        }

        $this->__saveSection();
        $section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        $configData = [
            'profile_id'    => $profileId,
            'section'       => $section,
            'website'       => $website,
            'store'         => $store,
            'groups'        => $this->__getGroupsForSave(),
        ];

        try {
            /** @var ProfileModel\Config $configModel */
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();

            $this->messageManager->addSuccessMessage(__('Profile configuration has been saved'));
        } catch (LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving profile configuration.'));
        }

        $this->__saveState($this->getRequest()->getPost('config_state'));

        return $configModel;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _delete()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid
            || !$isPost
            || !$profileId = $this->getRequest()->getParam('id')
        ) {
            $this->messageManager
                ->addErrorMessage(__('Profile could not be %1.',
                        $this->getRequest()->getParam('id')
                            ? 'found.'
                            : 'deleted.'
                    )
                );
            return $resultRedirect->setPath(self::PROFILE_BASE_URL);
        }

        try {
            $this->_profileRepository->deleteById($profileId);
            $this->messageManager->addSuccessMessage(__('Profile #%1 has been deleted.', $profileId));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath(self::PROFILE_BASE_URL);
    }


    /**
     * @todo implement wizard interface for collecting data after safe profile action
     *
     * @param Profile $profile
     * @param bool $force
     * @return bool
     */
    private function __collectConfigData(ProfileModel $profile, $force = false)
    {
        if ($this->getRequest()->getParam('id') && false === $force) {
            return false;
        }
        try {
            $this->_configSourceModel->collectWebStoreConfigs();

            if ($profile->getEntity() == ProfileModel\Type::TYPE_PRODUCT) {
                $this->_configSourceModel->collectVatConfigs();
                $this->_configSourceModel->collectItemSalesPrices();
            }

            if ($profile->getEntity() == ProfileModel\Type::TYPE_ORDER) {
                $this->_configSourceModel->collectVatConfigs();
                $this->_configSourceModel->collectItemSalesPrices();
            }

            $this->_configSourceModel->collectWarehouseConfigs();

            $profileTypeInstance = $profile->getTypeInstance();

            $this->_attributeFactory->setProfileEntity($profileTypeInstance);
            $this->_attributeFactory->collectProperties();

            $this->_categoryFactory->setProfileEntity($profileTypeInstance);
            $this->_categoryFactory->collect();

            return true;
        } catch ( LocalizedException $e) {

        } catch ( \Exception $e) {

        }

        return false;
    }

    /**
     * save config section
     */
    private function __saveSection()
    {
        $method = '_save' . $this->string
                ->upperCaseWords($this->getRequest()->getParam('section'), '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    /**
     * @return mixed
     */
    private function __getGroupsForSave()
    {
        $groups = $this->getRequest()->getParam('groups');
        $files = $this->getRequest()->getFiles('groups');

        if ($files && is_array($files)) {
            /**
             * Carefully merge $_FILES and $_POST information
             * None of '+=' or 'array_merge_recursive' can do this correct
             */
            foreach ($files as $groupName => $group) {
                $data = $this->_processNestedGroups($group);
                if (!empty($data)) {
                    if (!empty($groups[$groupName])) {
                        $groups[$groupName] = array_merge_recursive((array)$groups[$groupName], $data);
                    } else {
                        $groups[$groupName] = $data;
                    }
                }
            }
        }
        return $groups;
    }

    /**
     * @param array $configState
     * @return bool
     */
    private function __saveState($configState = [])
    {
        if (is_array($configState)) {
            $configState = $this->__sanitizeConfigState($configState);
            $adminUser = $this->_auth->getUser();
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = [];
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = [];
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }
        return true;
    }

    /**
     * @param $configState
     * @return mixed
     */
    private function __sanitizeConfigState($configState)
    {
        $sectionList = $this->_configStructure->getSectionList();
        $sanitizedConfigState = $configState;
        foreach ($configState as $sectionId => $value) {
            if (array_key_exists($sectionId, $sectionList)) {
                $sanitizedConfigState[$sectionId] = (bool)$sanitizedConfigState[$sectionId] ? '1' : '0';
            } else {
                unset($sanitizedConfigState[$sectionId]);
            }
        }
        return $sanitizedConfigState;
    }

    /**
     * @param $profileId
     * @param $actionCode
     * @param $status
     * @param $message
     * @throws LocalizedException
     */
    protected function _updateHistory($profileId, $actionCode, $status, $message)
    {
        $history = $this->_profileHistoryFactory->create();
        $historyData[] = [
            'profile_id'    => (int) $profileId,
            'action_code'   => $actionCode,
            'status'        => $status,
            'message'       => $message,
            'created_at'    => $this->_dateTime->gmtDate(),
        ];
        $history->getResource()->addRecord($historyData);
    }
}
