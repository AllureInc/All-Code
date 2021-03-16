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

class Delete extends AbstractPricing
{
    /**
     * save|update pricing data.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $returnToEdit = false;
        $pricingId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($pricingId) {
            try {
                $this->_pricingRepository->deleteById($pricingId);

                $this->messageManager->addSuccess(__("deleted successfully"));
                $resultRedirect->setPath('mpadvertisementmanager/pricing/index');
            } catch (\Magento\Framework\Validator\Exception $e) {
                $messages = $e->getMessages();
                $this->_addSessionErrorMessages($messages);
            } catch (\Exception $e) {
                $this->messageManager->addError(__("not able to delete ad pricing"));
            }
        } else {
            $this->messageManager->addError(__("invalid pricing id"));
            $resultRedirect->setPath('mpadvertisementmanager/pricing/edit', ['id'=>$pricingId]);
        }
        return $resultRedirect;
    }
}
