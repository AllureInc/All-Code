<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

use Plenty\Core\Api\Data\ProfileInterface;
use Plenty\Core\Helper\ImportExport as ImportExportHelper;
use Plenty\Core\Model\Source\Status;
use Plenty\Core\Plugin\ImportExport\Model\Import\Adapters\NestedArrayAdapterFactory;
use Plenty\Item\Model\Logger;

/**
 * Class Import
 * @package Plenty\Core\Plugin\ImportExport\Model
 */
class Import
{
    /**
     * @var NestedArrayAdapterFactory
     */
    private $_importAdapterFactory;

    /**
     * @var ImportExportHelper
     */
    private $_importExportHelper;

    /**
     * @var ProfileInterface
     */
    private $_profile;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager = null;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var array
     */
    private $_errors;

    /**
     * @var
     */
    private $errorMessages;

    /**
     * @var
     */
    private $validationResult;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var string
     */
    private $logTrace = "";

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    private $importModelFactory;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var DateTime
     */
    private $_date;

    /**
     * Import constructor.
     * @param ImportFactory $importModelFactory
     * @param ImportExportHelper $importExportHelper
     * @param NestedArrayAdapterFactory $nestedArrayAdapterFactory
     * @param ManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param Logger $logger
     */
    public function __construct(
        ImportFactory $importModelFactory,
        ImportExportHelper $importExportHelper,
        NestedArrayAdapterFactory $nestedArrayAdapterFactory,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        Logger $logger
    ) {
        $this->_importExportHelper = $importExportHelper;
        $this->_importAdapterFactory = $nestedArrayAdapterFactory;
        $this->_eventManager = $eventManager;
        $this->_date = $dateTime;
        $this->_logger = $logger;

        $this->importModelFactory = $importModelFactory;
        $this->settings = [
            // 'entity'                            => $this->_importExportHelper->getEntity(),
            // 'behavior'                          => $this->_importExportHelper->getBehavior(),
            'ignore_duplicates'                 => $this->_importExportHelper->getIgnoreDuplicates(),
            'validation_strategy'               => $this->_importExportHelper->getValidationStrategy(),
            'allowed_error_count'               => $this->_importExportHelper->getAllowedErrorCount(),
            'import_images_file_dir'            => $this->_importExportHelper->getImportFileDir(),
            'category_path_seperator'           => $this->_importExportHelper->getCategoryPathSeperator(),
            '_import_multiple_value_separator'  => ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
        ];
    }

    /**
     * @return string
     */
    public function getEntityCode()
    {
        return $this->settings['entity'];
    }

    /**
     * @param $entityCode
     * @return $this
     */
    public function setEntityCode($entityCode)
    {
        $this->settings['entity'] = $entityCode;
        return $this;
    }

    public function getBehaviour()
    {
        return $this->settings['behavior'];
    }

    /**
     * @param $behavior
     * @return $this
     */
    public function setBehavior($behavior)
    {
        $this->settings['behavior'] = $behavior;
        return $this;
    }

    /**
     * @param string $value
     */
    public function setIgnoreDuplicates($value)
    {
        $this->settings['ignore_duplicates'] = $value;
    }

    /**
     * @param string $strategy
     */
    public function setValidationStrategy($strategy)
    {
        $this->settings['validation_strategy'] = $strategy;
    }

    /**
     * @param int $count
     */
    public function setAllowedErrorCount($count)
    {
        $this->settings['allowed_error_count'] = $count;
    }

    /**
     * @param string $dir
     */
    public function setImportImagesFileDir($dir)
    {
        $this->settings['import_images_file_dir'] = $dir;
    }

    /**
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * @param ProfileInterface $profileEntity
     * @return $this
     */
    public function setProfile(ProfileInterface $profileEntity)
    {
        $this->_profile = $profileEntity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }

    /**
     * @return string
     */
    public function getLogTrace()
    {
        return $this->logTrace;
    }

    /**
     * @return mixed
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @param null $column
     * @return array
     */
    public function getErrors($column = null)
    {
        return $column
            ? array_column($this->_errors, $column)
            : $this->_errors;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function  getRequest()
    {
        if (!$this->_request) {
            throw new \Exception(__('Request data is not set.'));
        }
        return $this->_request;
    }

    /**
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Getter for default Delimiter
     * @return mixed
     */
    public function getMultipleValueSeparator()
    {
        return $this->settings['_import_multiple_value_separator'];
    }

    /**
     * Sets the default delimiter
     * @param $multipleValueSeparator
     */
    public function setMultipleValueSeparator($multipleValueSeparator)
    {
        $this->settings['_import_multiple_value_separator'] = $multipleValueSeparator;
    }

    /**
     * @return NestedArrayAdapterFactory
     */
    public function getImportAdapterFactory()
    {
        return $this->_importAdapterFactory;
    }

    /**
     * @param $importAdapterFactory
     */
    public function setImportAdapterFactory($importAdapterFactory)
    {
        $this->_importAdapterFactory = $importAdapterFactory;
    }

    /**
     * @param $dataArray
     * @return $this|bool
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        if (!$validation = $this->_validateData()) {
            throw new \Exception(__('Could not validate import data. Trace: %1', $this->logTrace));
        }

        $this->_importData();

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function _validateData()
    {
        $requestData = $this->getRequest();

        $importModel = $this->_createImportModel();
        $source = $this->_importAdapterFactory->create(
           [
               'data' => $requestData,
               'multipleValueSeparator' => $this->getMultipleValueSeparator()
           ]
        );
        $this->validationResult = $importModel->validateSource($source);

        $this->_addToLogTrace($importModel);
        return $this->validationResult;
    }

    /**
     * @throws LocalizedException
     */
    private function _importData()
    {
        $importModel = $this->_createImportModel();
        $importModel->importSource();

        switch ($this->getEntityCode()) {
            case 'catalog_product' :
                $this->_handleProductImportResult($importModel);
                break;
            case 'catalog_category' :
                $this->_handleCategoryImportResult($importModel);
                break;
        }
    }

    /**
     * @return ImportModel
     */
    private function _createImportModel()
    {
        $importModel = $this->importModelFactory->create();
        $importModel->setData($this->settings);
        return $importModel;
    }

    /**
     * @param $importModel
     */
    private function _addToLogTrace(ImportModel $importModel)
    {
        $this->logTrace = $this->logTrace . $importModel->getFormatedLogTrace();
    }

    /**
     * @param ImportModel $importModel
     * @return $this
     * @throws LocalizedException
     * @throws \Exception
     */
    private function _handleProductImportResult(ImportModel $importModel)
    {
        $errorAggregator = $importModel->getErrorAggregator();
        $this->errorMessages = $this->_importExportHelper->getImportErrorMessages($errorAggregator);

        $this->_addToLogTrace($importModel);

        if (!$importModel->getErrorAggregator()->hasToBeTerminated()) {
            $importModel->invalidateIndex();
        }

        if (!$errorAggregator->getErrorsCount()) {
            return $this;
        }

        $profileId = $this->getProfile()->getId();
        $request = $this->getRequest();
        $this->_errors = [];
        foreach ($errorAggregator->getAllErrors() as $error) {
            if (!isset($request[$error->getRowNumber()]['plenty_item_id'])
                || !isset($request[$error->getRowNumber()]['plenty_variation_id'])
            ) {
                continue;
            }

            $this->_errors[] = [
                'profile_id' => $profileId,
                'item_id' => $request[$error->getRowNumber()]['plenty_item_id'],
                'variation_id' => $request[$error->getRowNumber()]['plenty_variation_id'],
                'status' => Status::FAILED,
                'processed_at' => $this->_date->gmtDate(),
                'message' => __('%1 error. Code: %2. Columns effected: %3. Message: %4',
                    ucfirst($error->getErrorLevel()),
                    $error->getErrorCode(),
                    $error->getColumnName(),
                    $error->getErrorMessage()
                )
            ];
        }

        if (empty($this->getErrors())) {
            throw new \Exception(__('Product import was processed with some errors but errors could not have been retrieved from response data.'));
        }

        $this->_logger->error(
            __('Profile: %1 with process %2',
                $this->getProfile()->getName(),
                $this->getEntityCode()
            ), $this->getErrors()
        );

        $this->_eventManager->dispatch(
            'catalog_product_import_bunch_failed_after',
            ['import' => $this, 'bunch_failed' => $this->getErrors()]
        );

        throw new \Exception('Product import was processed with some errors. Please refer to log for more details.');
    }

    /**
     * @param ImportModel $importModel
     * @return $this
     * @throws LocalizedException
     */
    private function _handleCategoryImportResult(ImportModel $importModel)
    {
        $errorAggregator = $importModel->getErrorAggregator();
        $this->errorMessages = $this->_importExportHelper->getImportErrorMessages($errorAggregator);
        $this->_addToLogTrace($importModel);

        if (!$importModel->getErrorAggregator()->hasToBeTerminated()) {
            $importModel->invalidateIndex();
        }

        if (!$errorAggregator->getErrorsCount()) {
            return $this;
        }

        $profileId = $this->getProfile()->getId();
        $request = $this->getRequest();
        $errors = [];
        foreach ($errorAggregator->getAllErrors() as $error) {
            if (!isset($request[$error->getRowNumber()]['plenty_category_id'])) {
                continue;
            }

            $errors[] = [
                'profile_id' => $profileId,
                'category_id' => $request[$error->getRowNumber()]['plenty_category_id'],
                'status' => Status::FAILED,
                'processed_at' => $this->_date->gmtDate(),
                'message' => __('%1 error. Code: %2. Columns effected: %3. Message: %4',
                    ucfirst($error->getErrorLevel()),
                    $error->getErrorCode(),
                    $error->getColumnName(),
                    $error->getErrorMessage()
                )
            ];
        }

        $this->_logger->error(
            __('Profile: %1 with process %2',
                $this->getProfile()->getName(),
                $this->getEntityCode()
            ), $errors
        );

        $this->_eventManager->dispatch(
            'catalog_category_import_bunch_failed_after',
            ['import' => $this, 'bunch_failed' => $errors]
        );

        throw new \Exception('Category import was processed with some errors. Please refer to log for more details.');
    }
}
