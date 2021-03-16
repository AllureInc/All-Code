<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 * @deprecated since 2.1.0 as Salesrule form element moved to Ui Component based
 *
 *
 *
 */
namespace Kerastase\GiftRule\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminhtmlBlockSalesruleActionsPrepareform implements ObserverInterface
{
    /**
     * @var \ Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

    public function __construct(
        \Kerastase\GiftRule\Helper\Data $giftruleHelper
    ) {
        $this->_giftruleHelper          = $giftruleHelper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_giftruleHelper->isEnabled()) {
            return;
        }
        $this->_giftruleHelper->log(__METHOD__, true);
        $this->_giftruleHelper->log($observer->getEvent()->getName());
        
        /** @var  $form \Magento\Framework\Data\Form */
        $form   = $observer->getForm();

        /** @var $actionField \Magento\Framework\Data\Form\Element\Select */
        $actionField = $form->getElement('actions');

        $actions   = $actionField->getValues();
        $actions[] = [
            'label' => __("Buy X & Get N (Free Gift per Cart)"),
            'value' => \Kerastase\GiftRule\Helper\Data::BUY_X_GET_N_FREE_RULE,
        ];
        $actionField->setValues($actions);

        $fieldset = $form->getElement('action_fieldset');
        $fieldset->addField(
            'gift_sku',
            'text',
            [
                'name'  => 'gift_sku',
                'label' => __('Free Gift(SKU)'),
                'note'  => __('Enter the SKU of the free gift product'),
            ]
        );
    }
}
