<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Model\Salesrule\Action;

use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;

class BuyXGetN extends AbstractDiscount
{
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {   
        $customerSessionData = ObjectManager::getInstance()->create(CustomerSession::class);
        $logger = ObjectManager::getInstance()->create(\Psr\Log\LoggerInterface::class);
        $session_data = ['product_id'=> $item->getItemId(), 'product_name'=> $item->getName(), 'rule_id'=> $rule->getRuleId()];
        $validate = $rule->getConditions()->validate($item);
        if ($validate) {
            $customerSessionData->setKerastaceCartRuleDetail($session_data);
            $logger->info(print_r($session_data, true));
        }
        $customerSessionData->setParentProductId($item->getProductId());
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        return $discountData;
    }
}
