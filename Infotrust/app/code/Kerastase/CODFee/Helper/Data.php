<?php

namespace Kerastase\CODFee\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const COD_PAYMENT_CODE          = 'cashondelivery';
    const FEE_TOTAL_CODE            = 'cod_fee';

    const MODULE_NAMESPACE_ALIAS    = 'payment';

    const XML_PATH_ENABLED          = 'cashondelivery/active';
    const XML_PATH_DEBUG            = 'cashondelivery/debug';
    const XML_PATH_COD_FEE          = 'cashondelivery/fee';
    /**
     * @var \Magento\Framework\Logger\Monolog\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00
     */
    protected $_customLogger;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Logger\Monolog $customLogger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Kerastase\CODFee\Logger\Logger $customLogger,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->_customLogger            = $customLogger;
        $this->_moduleList              = $moduleList;

        parent::__construct($context);
    }

    /**
     * Get Config value
     *
     * @param $xmlPath
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($xmlPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_NAMESPACE_ALIAS . '/' . $xmlPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->isEnabled($storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDebugStatus($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_DEBUG, $storeId);
    }

    public function getExtensionVersion()
    {
        $moduleCode = 'Kerastase_CODFee';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     * Logging Utility
     *
     * @param $message
     * @param bool|false $useSeparator
     */
    public function log($message, $useSeparator = false)
    {
        if ($this->getDebugStatus()) {
            if ($useSeparator) {
                $this->_customLogger->addDebug(str_repeat('=', 100));
            }

            $this->_customLogger->addDebug($message);
        }
    }

    public function canApplyCodFee($quote)
    {
        $this->log(__METHOD__, true);
        if (!$this->isEnabled()) {
            return false;
        }

        $items = $quote->getAllItems();
        if (!count($items)) {
            return false;
        }
        if ($quote->getPayment()->getMethod()) {
            $this->log('hasQuotePayment::1');
            $paymentMethod = $quote->getPayment()->getMethodInstance();
            $this->log('QuotePayment::' . $paymentMethod->getCode());
            if ($paymentMethod->getCode() == self::COD_PAYMENT_CODE) {
                return true;
            }
        }
        return false;
    }

    public function getCodFee($storeId = null)
    {
        $codFee = $this->getConfigValue(self::XML_PATH_COD_FEE, $storeId);
        return $codFee;
    }
}