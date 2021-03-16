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

use Webkul\MpAdvertisementManager\Controller\RegistryConstants;
use Webkul\MpAdvertisementManager\Api\Data\PricingInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends AbstractPricing
{
    /**
     * Pricing edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $pricingId = $this->initCurrentPricing();
        $positionLabel = $this->_adsHelper->getPositionLabel($pricingId);
        $isExistingPricing = (bool)$pricingId;
        if ($isExistingPricing) {
            try {
                    //$this->_getSession()->setPricingFormData($pricingData);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while editing the pricing.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('mpadvertisementmanager/pricing/index');
                return $resultRedirect;
            }
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpAdvertisementManager::pricing');
        $this->prepareDefaultPricingTitle($resultPage);
        $resultPage->setActiveMenu('Webkul_MpAdvertisementManager::pricing');
        if ($isExistingPricing) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit %1', $positionLabel));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Pricing'));
        }
        return $resultPage;
    }

    /**
     * Pricing initialization
     *
     * @return string pricing id
     */
    protected function initCurrentPricing()
    {
        $pricingId = (int)$this->getRequest()->getParam('id');

        if ($pricingId) {
            $this->_coreRegistry->register(parent::CURRENT_PRICING_ID, $pricingId);
        }
        return $pricingId;
    }

    /**
     * Prepare pricing default title
     *
     * @param  \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultPricingTitle(
        \Magento\Backend\Model\View\Result\Page $resultPage
    ) {
        $resultPage->getConfig()->getTitle()->prepend(__('Pricing'));
    }
}
