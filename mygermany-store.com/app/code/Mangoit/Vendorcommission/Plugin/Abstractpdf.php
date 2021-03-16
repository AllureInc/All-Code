<?php

namespace Mangoit\Vendorcommission\Plugin;

class Abstractpdf 
{

    public function _getTotalsList()
    {
        die('6545465465465');
        $totals = $this->_pdfConfig->getTotals();
        echo "<pre>";
        print_r($_GET);
        die('died');
        usort($totals, [$this, '_sortTotalsList']);
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            if ($totalInfo['title'] == 'Grand Total') {
                $totalInfo['title'] = 'Grand Total (Inc. all Taxes)';
            }
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->_pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }
        

        return $totalModels;
    }
}