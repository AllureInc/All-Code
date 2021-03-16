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
namespace Webkul\MpAdvertisementManager\Controller\Advertise;

class EnableAdsDemo extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    /**
     * execute enable ads demo on frontend
     *
     * @return json
     */
    public function execute()
    {
        $returnArray = [];
        $isEnabled = (int)$this->getRequest()->getPostValue("isEnable");
        
        if ($isEnabled == 1) {
            $isEnable = $this->_getSession()->setEnableAdsDemo(0);
            $returnArray['message'] = __("successfully disabled");
            $returnArray['status'] = 0;
        } else {
            $this->_getSession()->setEnableAdsDemo(1);
            $returnArray['message'] = __("successfully enabled");
            $returnArray['status'] = 1;
        }

        return $this->getJsonResponse($returnArray);
    }
}
