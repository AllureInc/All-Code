<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Controller\Adminhtml\Pricing;

class DeleteFromList extends AbstractPricing
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getParams();
        if (isset($post['excluded'])) {
            $pricingIds = $this->_pricingCollection->create();
        }
        if (isset($post['selected'])) {
            $selectedIds  = $this->getRequest()->getParam('selected');

            $pricingIds = $this->_pricingCollection->create()
                                ->addFieldToFilter('id', ['in' => $selectedIds]);
        }
        $size = $pricingIds->getSize();
        try {
            foreach ($pricingIds as $pricingId) {
                $pricingId->delete();
            }
            $this->messageManager->addSuccess(
                __('Total of %1 record(s) were successfully deleted', $size)
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect->setPath('mpadvertisementmanager/pricing/index');
        return $resultRedirect;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAdvertisementManager::pricing');
    }
}
