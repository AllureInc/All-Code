<?php
/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
namespace Onestepcheckout\Iosc\Model\Output;

use Magento\Framework\Exception\LocalizedException;

class CartUpdate implements OutputManagementInterface
{

    public function getOutputKey()
    {
        return 'updateqty';
    }

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Onestepcheckout\Iosc\Helper\Data $helper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Onestepcheckout\Iosc\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritDoc}
     * @see \Onestepcheckout\Iosc\Model\Input\InputManagement::processPayload()
     */
    public function processPayload($input)
    {

        $data = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if ($quote->getId()) {
            $input = $input[$this->getOutputKey()] ?? false;

            if (isset($input['item_id']) && isset($input['qty'])) {
                if ((double)$input['qty'] > 0) {
                    if ($item = $quote->getItemById((int)$input['item_id'])) {
                        $item->setQty((double)$input['qty']);
                        if ($item->getHasError()) {
                            throw new LocalizedException(__($item->getMessage()));
                        }
                    }
                } else {
                    $quote->removeItem((int)$input['item_id']);
                    if ($quote->hasItems() == 0) {
                        $quote
                            ->setTotalsCollectedFlag(false)
                            ->collectTotals();
                    }
                }

                $data['cart_items'] = (int)$quote->hasItems();
            }
        }
        return $data;
    }
}
