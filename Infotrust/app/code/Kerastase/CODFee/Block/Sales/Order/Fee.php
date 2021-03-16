<?php

namespace Kerastase\CODFee\Block\Sales\Order;

/**
 * Customer total block for order
 *
 */
class Fee extends \Magento\Framework\View\Element\Template
{
    protected $CodHelper = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Kerastase\CODFee\Helper\Data $CodHelper,
        array $data = []
    ) {
        $this->CodHelper = $CodHelper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize custom totals for order*
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $order  = $parent->getOrder();
        #$source = $parent->getSource();

        if ($order->getCodFee()) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code'       => \Kerastase\CODFee\Helper\Data::FEE_TOTAL_CODE,
                    'label'      => __('Cash on Delivery'),
                    'value'      => $order->getCodFee(),
                    'base_value' => $order->getBaseCodFee()
                ]
            );
            $parent->addTotalBefore($total, 'tax');
        }

        return $this;
    }
}