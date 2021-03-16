<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model;

use \Magento\Framework\Model\AbstractModel;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class WalletTransferData extends AbstractModel
{
    /**
     * @var SessionManagerInterface
     */
    protected $_sessionManager;
    /**
     * @var CustomerSession
     */
    protected $_customerSession;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param SessionManagerInterface                $sessionManager
     * @param CustomerSession                        $customerSession
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     * @param WebkulWalletsystemHelperData           $walletHelper
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        CustomerSession $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->_sessionManager = $sessionManager;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->_walletHelper = $walletHelper;
    }

    /**
     * Get wallet code from session
     * @return value
     */
    public function getWalletTransferDataToSession()
    {
        return $this->_customerSession->getWalletTransferData();
    }
    /**
     * SET wallet code from session
     * @return value
     */
    public function setWalletTransferDataToSession($value)
    {
        $this->_customerSession->setWalletTransferData($value);
    }

    public function deleteWalletTransferDataToSession()
    {
        $this->_customerSession->setWalletTransferData('');
    }

    public function checkAndUpdateSession()
    {
        $validationDuration = $this->_walletHelper->getCodeValidationDuration();
        $sessionData = $this->_walletHelper->convertStringAccToVersion($this->getWalletTransferDataToSession(), 'decode');
        if (is_array($sessionData) && array_key_exists('created_at', $sessionData) && !empty($sessionData['created_at'])) {
            $currentTime = $this->_date->gmtDate();
            $difference =  strtotime($currentTime) - $sessionData['created_at'];
            if ($difference > $validationDuration) {
                $this->deleteWalletTransferDataToSession();
            }
        }
    }
}
