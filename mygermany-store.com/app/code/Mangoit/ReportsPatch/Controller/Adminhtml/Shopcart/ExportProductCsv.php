<?php

namespace Mangoit\ReportsPatch\Controller\Adminhtml\Shopcart;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductCsv extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Export products report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'wishlist_products.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Mangoit\ReportsPatch\Block\Adminhtml\Wishlist\Grid::class
        )->getCsvFile();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
