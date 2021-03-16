<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\SalesRule\Model\Rule;

class SalesQuoteCollectTotalsBefore implements ObserverInterface
{
    /**
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var CustomerSession
     */
    protected $_rule;

    /**
     * @var \Magento\Framework\App\RequestInterface
    */
    protected $_request;
    
    public function __construct(
        \Kerastase\GiftRule\Helper\Data $giftruleHelper,
        \Magento\Customer\Model\Session $customerSession,
        RequestInterface $request,
        Rule $rule
    ) {
        $this->_giftruleHelper          = $giftruleHelper;
        $this->_customerSession         = $customerSession;
        $this->_rule                    = $rule;
        $this->_request = $request;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_giftruleHelper->isEnabled()) {
            return;
        }
        $this->_giftruleHelper->log(__METHOD__, true);
        $this->_resetFreeItems($observer->getEvent()->getQuote());
    }

    /**
     * Delete all free items from the cart before the rule is applied.
     *
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function _resetFreeItems(\Magento\Quote\Model\Quote $quote)
    {
        $gift_array_id = $this->_giftruleHelper->getGiftProductData($quote);
        $isRemoved = $this->_customerSession->getIsItemRemoved();
        $postValues = $this->_request->getPostValue();
                
        /*------------- For updating the items qty | Start -------------*/
        $rule_collection = $this->_rule->getCollection()->addFieldToFilter('simple_action', ['eq'=> \Kerastase\GiftRule\Helper\Data::BUY_X_GET_N_FREE_RULE]);
        $all_items = $quote->getAllItems();
        foreach ($rule_collection as $rule) {
            foreach ($all_items as $quoteItem) {
                $this->_giftruleHelper->log('------- Line 74 --------');
                $validate_old = $rule->getConditions()->validate($quoteItem);
                $validate = $rule->getActions()->validate($quoteItem);
                $this->_giftruleHelper->log('### Validate | Is Action Set : '.$validate);
                $this->_giftruleHelper->log('### Validate2 | Is Condition Set : '.$validate_old);
                if(!$validate){
                    try {
                        $this->_giftruleHelper->log(' Now item '.$quoteItem->getName().' is not eligible.');
                        $this->_giftruleHelper->removeGiftItems($quote, $quoteItem->getProductId(), $gift_array_id);
                    } catch (Exception $e) {
                        $this->_giftruleHelper->log(' Exception occurred: Line 84| '.$e->getMessage());    
                    }
                    $this->_giftruleHelper->log(print_r($rule->getData(), true));
                } else {
                    $this->_giftruleHelper->log('----- Gift Items Will Be Added | For : '.$quoteItem->getName());
                }
            }
        }
        /*------------- For updating the items qty | Ends -------------*/

        if ($isRemoved == 1) {
            $postValues = $this->_request->getPostValue();
            $itemId = '';
            $productId = '';
            if (array_key_exists('item_id', $postValues)) {
                $itemId = $postValues['item_id'];
                $productId = $this->_giftruleHelper->getQuoteData($quote->getId(), $itemId);
                $this->_giftruleHelper->log('#1. Item ID: '.$itemId);
            }
            elseif (array_key_exists('id', $postValues)) {
                $itemId = $postValues['id'];
                $productId = $this->_giftruleHelper->getQuoteData($quote->getId(), $itemId);
                $this->_giftruleHelper->log('#2. Item ID: '.$itemId);
            }
            
            foreach ($quote->getAllItems() as $item) {
                foreach ($item->getOptions() as $option) {
                    if ($option->getValue() == $productId) {
                        if (in_array($item->getProductId(), $gift_array_id)) {
                            $quote->removeItem($item->getId());
                            $item->save();
                        }
                        $quote->save();
                        $isRemoved = $this->_customerSession->unsIsItemRemoved();
                    }
                }
            }
        }
    }
}
