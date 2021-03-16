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

class Save extends AbstractPricing
{
    /**
     * save|update pricing data.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $pricingId = isset($originalRequestData['ads_pricing']['pricing_id'])
            ? $originalRequestData['ads_pricing']['pricing_id']
            : null;
        if ($originalRequestData) {
            try {
                $pricingData = $originalRequestData['ads_pricing'];

                $isExistingPricing = (bool) $pricingId;

                $pricing = $this->_pricingDataFactory->create();

                if ($isExistingPricing) {
                    $pricingData['id'] = $pricingId;
                }
                $pricingData['updated_at'] = $this->getTime();
                if (!$isExistingPricing) {
                    $pricingData['created_at'] = $this->getTime();
                }
                $pricing->setData($pricingData);

                // Save banner
                if ($isExistingPricing) {
                    $this->_pricingRepository->save($pricing);
                } else {
                    $pricing = $this->_pricingRepository->save($pricing);
                    $pricingId = $pricing->getId();
                }

                $this->_getSession()->unsPricingFormData();

                // Done Saving pricing, finish save action
                $this->_coreRegistry->register(parent::CURRENT_PRICING_ID, $pricingId);
                $this->messageManager->addSuccess(__('Pricing Created.'));
                $returnToEdit = (bool) $this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $e) {
                $messages = $e->getMessages();
                if (empty($messages)) {
                    $messages = $e->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setPricingFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the pricing. %1', $e->getMessage())
                );
                $this->_getSession()->setPricingFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($pricingId) {
                $resultRedirect->setPath(
                    'mpadvertisementmanager/pricing/edit',
                    ['id' => $pricingId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'mpadvertisementmanager/pricing/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('mpadvertisementmanager/pricing/index');
        }

        return $resultRedirect;
    }
    public function getTime()
    {
        $today = $this->_timezoneInterface->date()->format('m/d/y H:i:s');
        
        // for convert date time according to magento time zone
        $dateTimeAsTimeZone = $this->_timezoneInterface
                                        ->date(new \DateTime(date("Y/m/d h:i:sa")))
                                        ->format('m/d/y H:i:s');
                                        return $dateTimeAsTimeZone;
    }
}
