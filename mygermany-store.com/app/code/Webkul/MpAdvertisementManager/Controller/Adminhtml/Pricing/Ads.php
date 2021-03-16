<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Controller\Adminhtml\Pricing;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\InputException;

class Ads extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MpAdvertisementManager::pricing';

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * __construct
     *
     * @param Context $context
     * @param \Webkul\MpAdvertisementManager\Helper\Data $adsHelper
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper
    ) {
        $this->_adsHelper = $adsHelper;
        parent::__construct($context);
    }

    /**
     * ajax save tax class.
     *
     * @return json
     */
    public function execute()
    {
        try {
            $settings = $this->getRequest()->getPost('ads');
            $status = $this->_adsHelper->saveAdsSettings($settings);
            $responseContent = [
                'success' => true,
                'error_message' => $status
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('there is some error in saving ads settings.')
            ];
        }
        
        /**
         * @var \Magento\Framework\Controller\Result\Json $resultJson
         */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }

   
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
