<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderRestore
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderRestore\Model;

use \Magento\Sales\Model\Order as SalesOrder;

class Order extends SalesOrder
{
    public function restore($comment = '', $state)
    {
        if ($this->isCanceled()) {
            $productStockQty = [];
            foreach ($this->getAllVisibleItems() as $item) {
                $productStockQty[$item->getProductId()] = $item->getQtyCanceled();
                foreach ($item->getChildrenItems() as $child) {
                    $productStockQty[$child->getProductId()] = $item->getQtyCanceled();
                    $child->setQtyCanceled(0);
                    $child->setTaxCanceled(0);
                    $child->setDiscountTaxCompensationCanceled(0);
                }
                $item->setQtyCanceled(0);
                $item->setTaxCanceled(0);
                $item->setDiscountTaxCompensationCanceled(0);
            }

            $this->setSubtotalCanceled(0);
            $this->setBaseSubtotalCanceled(0);

            $this->setTaxCanceled(0);
            $this->setBaseTaxCanceled(0);

            $this->setShippingCanceled(0);
            $this->setBaseShippingCanceled(0);

            $this->setDiscountCanceled(0);
            $this->setBaseDiscountCanceled(0);

            $this->setTotalCanceled(0);
            $this->setBaseTotalCanceled(0);

            $this->setState($state)
                ->setStatus($this->getConfig()->getStateDefaultStatus($state));
            if (!empty($comment)) {
                $this->addStatusHistoryComment($comment);
            }

        }

        return $this;
    }


} 