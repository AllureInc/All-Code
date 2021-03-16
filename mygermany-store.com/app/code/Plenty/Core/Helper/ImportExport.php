<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Helper;;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ImportExport
 * @package Plenty\Core\Helper
 */
class ImportExport extends AbstractHelper
{
    const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = '*/history/download';

    /**
     * Limit view errors
     */
    const LIMIT_ERRORS_MESSAGE = 100;

    const XML_PATH_IGNORE_DUPLICATES            = 'plenty_item/default/ignore_duplicates';
    const XML_PATH_BEHAVIOR                     = 'plenty_item/default/behavior';
    const XML_PATH_ENTITY                       = 'plenty_item/default/entity';
    const XML_PATH_VALIDATION_STRATEGY          = 'plenty_item/default/validation_strategy';
    const XML_PATH_ALLOWED_ERROR_COUNT          = 'plenty_item/default/allowed_error_count';
    const XML_PATH_IMPORT_IMAGES_FILE_FIR       = 'plenty_item/default/import_images_file_dir';
    const XML_PATH_CATEGORY_PATH_SEPERATOR      = 'plenty_item/default/category_path_seperator';

    /**
     * @var \Magento\ImportExport\Model\Report\ReportProcessorInterface
     */
    protected $reportProcessor;

    /**
     * @var \Magento\ImportExport\Model\History
     */
    protected $historyModel;

    /**
     * @var \Magento\ImportExport\Helper\Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\Backend\Helper\Data
     */

    /**
     * ImportExport constructor.
     * @param Context $context
     * @param ReportProcessorInterface $reportProcessor
     * @param History $historyModel
     * @param Report $reportHelper
     */
    public function __construct(
        Context $context,
        ReportProcessorInterface $reportProcessor,
        History $historyModel,
        Report $reportHelper
    ) {
        $this->reportProcessor = $reportProcessor;
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
        // $this->_helper = $helper;

        parent::__construct($context);
    }

    public function getCategoryPathSeperator() {
        return $this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PATH_SEPERATOR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getIgnoreDuplicates()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_IGNORE_DUPLICATES, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getBehavior()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BEHAVIOR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENTITY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getValidationStrategy()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VALIDATION_STRATEGY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getAllowedErrorCount()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ALLOWED_ERROR_COUNT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getImportFileDir()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_IMPORT_IMAGES_FILE_FIR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * TODO: Refactor Code
     * @param \Magento\Framework\View\Element\AbstractBlock $resultBlock
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    public function getImportErrorMessages(
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $resultText = '';
        if ($errorAggregator->getErrorsCount()) {
            $message = '';
            $counter = 0;
            foreach ($this->getErrorMessages($errorAggregator) as $error) {
                $message .= ++$counter . '. ' . $error . '<br>';
                if ($counter >= self::LIMIT_ERRORS_MESSAGE) {
                    break;
                }
            }
            if ($errorAggregator->hasFatalExceptions()) {
                foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                    $message .= $error->getErrorMessage()
                        . __('Additional data') . ': '
                        . $error->getErrorDescription() . '</div>';
                }
            }
            try {
                $resultText.=
                    '<strong>' . __('Following Error(s) has been occurred during importing process:') . '</strong><br>'
                    . '<div class="import-error-wrapper">' . __('Only first 100 errors are displayed here. ')
                    . '<a href="'
                    //. $this->createDownloadUrlImportHistoryFile($this->createErrorReport($errorAggregator))
                    . '">' . __('Download full report') . '</a><br>'
                    . '<div class="import-error-list">' . $message . '</div></div>'
                ;
            } catch (\Exception $e) {
                foreach ($this->getErrorMessages($errorAggregator) as $errorMessage) {
                    $resultText.= $errorMessage;
                }
            }
        }

        return $resultText;
    }

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @return array
     */
    protected function getErrorMessages(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $messages = [];
        $rowMessages = $errorAggregator->getRowsGroupedByErrorCode([], [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
        foreach ($rowMessages as $errorCode => $rows) {
            $messages[] = $errorCode . ' ' . __('in rows:') . ' ' . implode(', ', $rows);
        }
        return $messages;
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError[]
     */
    protected function getSystemExceptions(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $errorAggregator->getErrorsByCode([AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getImportedFile());
        $writeOnlyErrorItems = true;
        if ($this->historyModel->getData('execution_time') == History::IMPORT_VALIDATION) {
            $writeOnlyErrorItems = false;
        }
        $fileName = $this->reportProcessor->createReport($sourceFile, $errorAggregator, $writeOnlyErrorItems);
        $this->historyModel->addErrorReportFile($fileName);
        return $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function createDownloadUrlImportHistoryFile($fileName)
    {
        return $this->getUrl(self::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_getUrl($route, $params);
    }
}
