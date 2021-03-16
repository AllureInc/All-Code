<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile;


use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

use Plenty\Core\Model\Profile;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Model\ResourceModel\Profile\CollectionFactory;
use Plenty\Core\Model\Profile\ConfigFactory;

/**
 * Class DataProvider
 * @package Plenty\Core\Model\Profile
 */
class DataProvider extends AbstractDataProvider
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var profileFactory
     */
    private $profileFactory;

    /**
     * @var \Plenty\Core\Model\Profile\ConfigFactory
     */
    private $configFactory;

    /**
     * @var \Plenty\Core\Model\ResourceModel\Profile\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestScopeFieldName = 'store';

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $registry
     * @param RequestInterface $request
     * @param ProfileFactory $profileFactory
     * @param LoggerInterface $logger
     * @param \Plenty\Core\Model\Profile\ConfigFactory $configFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        Registry $registry,
        RequestInterface $request,
        ProfileFactory $profileFactory,
        LoggerInterface $logger,
        ConfigFactory $configFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
        $this->registry = $registry;
        $this->request = $request;
        $this->profileFactory = $profileFactory;
        $this->logger = $logger;

        $this->configFactory = $configFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return mixed|Profile
     * @throws NoSuchEntityException
     */
    public function getCurrentProfile()
    {
        $profile = $this->registry->registry('plenty_profile');
        if ($profile) {
            return $profile;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        if ($requestId) {
            $profile = $this->profileFactory->create();
            $profile->load($requestId);
            if (!$profile->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $profile;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        /** @var Profile $profile */
        foreach ($items as $profile) {
            $result['profile'] = $profile->getData();
            $this->loadedData[$profile->getId()] = $result;
        }

        return $this->loadedData;
    }
}
