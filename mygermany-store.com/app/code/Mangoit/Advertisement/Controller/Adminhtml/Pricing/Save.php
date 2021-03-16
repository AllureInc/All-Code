<?php


namespace Mangoit\Advertisement\Controller\Adminhtml\Pricing;

class Save extends \Webkul\MpAdvertisementManager\Controller\Adminhtml\Pricing\Save
{

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');
        $session->start();
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        
        // $data =  $session->getAdvertise();
        // $session->unsAdvertise();
        // echo "<pre>";

        // print_r($originalRequestData);
        // die("d,,");
        // if (!empty($data)) {
            $originalRequestData['ads_pricing']['ad_type'] = $originalRequestData['mis_adv_content']['ad_type'];
            $originalRequestData['ads_pricing']['content_type'] = $originalRequestData['mis_adv_content']['content_type'];
        // }
        // echo "<br> new data: ";
        // print_r($originalRequestData);
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
                // print_r($pricingData);
                $pricing->setData($pricingData);
                // die();

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
                $data =  $session->getAdvertise();
                if (isset($data)) {
                    // if ($this->saveSessionData($originalRequestData, $pricingId)) {
                        $returnToEdit = (bool) $this->getRequest()->getParam('back', false);
                        # code...
                    // }
                } else {
                    $returnToEdit = (bool) $this->getRequest()->getParam('back', false);
                }
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
        // die("saved");
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

    public function  saveSessionData($originalRequestData, $pricingId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adModel = $objectManager->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $session = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');
        $data =  $session->getAdvertise();
        if (isset($data)) {
            $blockId = $data['block'];
            // echo "<br> content_type ".$data['content_type'];
            $contentType = $data['content_type'];
            // echo "<br> ad_type ".$data['ad_type'];
            $adType = $data['ad_type'];
            // print_r($data);
            // print_r($originalRequestData['ads_pricing']);
            $modelData = $adModel->load($pricingId);
                if (count($modelData->getData() >= 1)) {
                    $modelData->setContentType($contentType);
                    $modelData->setAdType($adType);
                    $modelData->save();                
                }
            
        }
        $session->unsAdvertise();
        return true;
    }
}
