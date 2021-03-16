<?php

namespace Kerastase\CODFee\Controller\Cod;

class Apply extends \Kerastase\CODFee\Controller\Cod
{
    public function execute()
    {
        try {
            $paymentMethod = $this->getRequest()->getParam('payment_method');
            $quote = $this->cart->getQuote();
            $quote->getPayment()->setMethod($paymentMethod['method']);
            $quote->setTriggerRecollect(1); //$quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->save();

            $result = [
                'success' => true,
                'message' => 'Quote total re-collected.'.$paymentMethod['method']
            ];

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
        echo json_encode($result);
    }
}
