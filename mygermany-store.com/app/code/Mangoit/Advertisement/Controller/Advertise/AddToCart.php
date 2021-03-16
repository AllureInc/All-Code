<?php

namespace Mangoit\Advertisement\Controller\Advertise;

class AddToCart extends \Webkul\MpAdvertisementManager\Controller\AbstractAds
{
    const PRODUCT_SKU = "wk_mp_ads_plan";

    /**
     * execute add ad plans to the cart
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $customerId = $this->_getSession()->getId();
        $blockId = $this->getRequest()->getParam("blockId");
        $formKey = $this->getRequest()->getParam("form_key");
        $wholedata = $this->getRequest()->getParam('book');
        
        $settings = $this->_helper->getSettingsById($blockId);
        $positionsCount =  isset($settings['sort_order'])?$settings['sort_order']:1;
        $bookedPositions = $this->_orderHelper->getBookedAdsCount($blockId);

        if (isset($wholedata[$blockId]['block']) && $wholedata[$blockId]['block']) {
            if ($bookedPositions >= $positionsCount) {
                $this->messageManager->addError(__("this ad position is already max sold out, please book other"));
                $this->_redirect("mpads/advertise/");
            } else {
                $wholedata[$blockId]['days'] = isset($settings['valid_for'])?$settings['valid_for']:1;

                $option = $this->createCustomOption($wholedata, $blockId);
                $additionalOptions = $this->createAdditionalOptions($wholedata, $blockId);
                $planProduct = $this->_objectManager->get("\Magento\Catalog\Model\ProductRepository")->get(self::PRODUCT_SKU);
                $planProduct->addCustomOption('additional_options', $additionalOptions);
                $quoteId = $this->_cartManager->createEmptyCartForCustomer($customerId);

                $cart = $this->_cartItemManager->getList($quoteId);
                $cartObj = $this->_objectManager->get("Magento\Checkout\Model\Cart");
                $itemId = 0;
                if (count($cart) > 0) {
                    foreach ($cart as $cartItem) {
                        if ($cartItem->getOptionByCode('block_id_'.$blockId)) {
                            $itemId = $cartItem->getId();
                            $this->messageManager->addWarning(__("item already added to the cart"));
                        }
                    }
                }
                if ($itemId) {
                    $this->_cartDataItem->setItemId($itemId);
                }
                $quote = $this->_quoteFactory->create()->load($quoteId);
                $this->_cartDataItem->setSku($planProduct->getSku());
                $this->_cartDataItem->setProductId($planProduct->getId());
                $this->_cartDataItem->setQuote($quote);
                $this->_cartDataItem->setQuoteId($quoteId);
                $this->_cartDataItem->setQty(1);
                $this->_cartDataItem->setName($planProduct->getName());
                $this->_cartDataItem->setProductType($planProduct->getTypeId());
                $this->_cartDataItem->setPrice($wholedata[$blockId]['price']);
    
                try {
                    $item = $this->_cartItemManager->save($this->_cartDataItem);

                    $item->setCustomBasePriceForAdvProduct($wholedata[$blockId]['base_price']);
                    $item->setCustomPrice($wholedata[$blockId]['price']);
                    $item->setOriginalCustomPrice($wholedata[$blockId]['price']);
                    $item->addOption(['code'=>'info_buyRequest', 'value'=> $this->_serializer->serialize($wholedata)]);
                    $item->addOption(['code'=>'block_id_'.$blockId, 'value'=> $this->_serializer->serialize($option)]);
                    $item->save();

                    // $this->_objectManager->get("Magento\Checkout\Model\Cart")->save();
                    $cartObj->setQuote($quote);
                    $cartObj->saveQuote($quote);
                    $cartObj->save();

                    $this->_redirect("checkout/cart/");
                } catch (\Exception $e) {
                    $this->messageManager->addWarning(__("unable to add product to the cart"));
                    $this->_redirect("mpads/advertise/");
                }
            }
        } else {
            $this->messageManager->addError(__("no ad block selected"));
            $this->_redirect("mpads/advertise/");
        }

        /**
         * @var \Magento\Framework\View\Result\Page $resultPage
         */
        return $resultPage;
    }

    /**
     * createCustomOption create custom option
     *
     * @param  array $wholedata
     * @param  int   $blockId
     * @return array
     */
    public function createCustomOption($wholedata, $blockId)
    {
        return ['code'=>'block_id_'.$blockId, 'value'=> $this->_serializer->serialize($wholedata)];
    }

    /**
     * createAdditionalOptions additional data to show on cart page
     *
     * @param  array $options
     * @param  int   $blockId
     * @return array
     */
    public function createAdditionalOptions($options, $blockId)
    {
        if (isset($options[$blockId])) {
            $values =  $options[$blockId];
            $addOptions = [];
            array_push($addOptions, ['label' => __('Ad Position'), 'value' => $this->_helper->getPositionLabel($values['block_position'])]);
            array_push($addOptions, ['label' => __('Valid Days'), 'value' => $values['days']]);
            array_push($addOptions, ['label' => __('Block'), 'value' => $this->_helper->getBlockLabel($values['block'])]);
            return $this->_serializer->serialize($addOptions);
        } else {
            throw new \Exception(__("unable to add the product in cart"));
        }
    }
}
