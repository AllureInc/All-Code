<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model Class
 * For retrieving cart data
 */
namespace Cnnb\Gtm\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Cnnb\Gtm\DataLayer\QuoteData\QuoteItemProvider;
use Cnnb\Gtm\DataLayer\QuoteData\QuoteProvider;
use Cnnb\Gtm\Helper\DataLayerItem as dataLayerItemHelper;
use Cnnb\Gtm\Helper\Data as GtmHelper;

class Cart extends DataObject
{

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var dataLayerItemHelper
     */
    protected $_dataLayerItemHelper;

    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var QuoteProvider
     */
    protected $_quoteProvider;

    /**
     * @var QuoteItemProvider
     */
    protected $_quoteItemProvider;

    /**
     * @var GtmHelper
     */
    protected $_gtmHelper;

    /**
     * Cart constructor.
     * @param CheckoutSession $checkoutSession
     * @param dataLayerItemHelper $dataLayerItemHelper
     * @param Escaper $escaper
     * @param QuoteProvider $quoteProvider
     * @param QuoteItemProvider $quoteItemProvider
     * @param array $data
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        dataLayerItemHelper $dataLayerItemHelper,
        Escaper $escaper,
        QuoteProvider $quoteProvider,
        QuoteItemProvider $quoteItemProvider,
        GtmHelper $gtmHelper,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_dataLayerItemHelper = $dataLayerItemHelper;
        $this->_escaper = $escaper;
        $this->_quoteProvider = $quoteProvider;
        $this->_quoteItemProvider = $quoteItemProvider;
        parent::__construct($data);
        $this->_gtmHelper = $gtmHelper;
    }

    /**
     * Get cart array
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCart()
    {
        $quote = $this->getQuote();
        $cart = [];

        $cart['hasItems'] = false;

        if ($quote->getItemsCount()) {
            $items = [];
            
            foreach ($quote->getAllVisibleItems() as $item) {
                $getAttribute = $this->_gtmHelper->getAttributeValue($item->getProduct());
                $itemData = [
                    'product_id' => $item->getProduct()->getEntityId(),
                    'item_id' => $item->getItemId(),
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'parent_sku' => $item->getProduct() ? $item->getProduct()->getData('sku') : $item->getSku(),
                    'product_type' => $item->getProductType(),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQty() * 1,
                    'brand' => $getAttribute['brand'],
                ];
                foreach ($getAttribute as $attr_key => $attr_value) {
                    $itemData[$attr_key] = $attr_value;
                }
                if (!empty($category = $this->_dataLayerItemHelper->getCategories($item))) {
                    $itemData['category'] = $this->_dataLayerItemHelper->getFirstCategory($item);
                }
                $items[] = $this->_quoteItemProvider
                                ->setItem($item)
                                ->setItemData($itemData)
                                ->setActionType(QuoteItemProvider::ACTION_VIEW_CART)
                                ->setListType(QuoteItemProvider::LIST_TYPE_GENERIC)
                                ->getData();
            }

            if (count($items) > 0) {
                $cart['hasItems'] = true;
                $cart['items'] = $items;
            }

            $cart['total'] = $this->_dataLayerItemHelper->formatPrice($quote->getGrandTotal());
            $cart['itemCount'] = $quote->getItemsCount() * 1;
            $cart['cartQty'] = $quote->getItemsQty() * 1;
        }
        return $this->_quoteProvider->setQuote($this->getQuote())->setTransactionData($cart)->getData();
    }

    /**
     * Get active quote
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
