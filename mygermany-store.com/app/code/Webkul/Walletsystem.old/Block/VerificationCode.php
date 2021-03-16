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
namespace Webkul\Walletsystem\Block;

use Webkul\Walletsystem\Model\ResourceModel\Walletrecord;

class VerificationCode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Walletrecord
     */
    private $_walletrecordModel;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $_pricingHelper;
    /**
     * @param MagentoFrameworkViewElementTemplateContext $context
     * @param WalletrecordCollectionFactory              $walletrecordModel
     * @param WebkulWalletsystemHelperData               $walletHelper
     * @param MagentoFrameworkPricingHelperData          $pricingHelper
     * @param [type]                                     $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_walletrecordModel = $walletrecordModel;
        $this->_walletHelper = $walletHelper;
        $this->_pricingHelper = $pricingHelper;
    }
    /**
     * use to get current url.
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    /**
     * getIsSecure check is secure or not
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }
    // get transfered parameterd passed in request
    public function getTransferParameters()
    {
        $params = [];
        $getEncodedParamData = $this->getRequest()->getParam('parameter');
        $paramsJson = base64_decode(urldecode($getEncodedParamData));
        if ($paramsJson) {
            $params = json_decode($paramsJson, true);
        }
        return $params;
    }
    // get remaining total of a customer
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecordCollection = $this->_walletrecordModel->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $record) {
                $remainingAmount = $record->getRemainingAmount();
            }
        }
        return $this->_pricingHelper
            ->currency($remainingAmount, true, false);
    }
}
