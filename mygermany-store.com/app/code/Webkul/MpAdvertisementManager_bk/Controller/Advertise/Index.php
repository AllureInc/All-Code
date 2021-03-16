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

class Index extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    public function execute()
    {
        $pageLabel = 'Buy Advertisement Positions';
        $resultPage = $this->_resultPageFactory->create();
        if ($this->_mpHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpads_advertise_index_layout2');
        }
        $resultPage->getConfig()->getTitle()->set(__($pageLabel));
        return $resultPage;
    }
}
