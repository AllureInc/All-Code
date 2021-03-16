<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Helper;

class BarCodes extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct (
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function barCodeForPackagingSlip($incrementId) {
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        // $barCodeImage = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($incrementId, $generator::TYPE_CODE_128)) . '">';
        return base64_encode($generator->getBarcode($incrementId, $generator::TYPE_CODE_128));
    }
}