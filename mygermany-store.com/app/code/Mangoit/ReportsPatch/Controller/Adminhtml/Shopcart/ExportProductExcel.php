<?php

namespace Mangoit\ReportsPatch\Controller\Adminhtml\Shopcart;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportProductExcel extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart
{
    /**
     * Export products report to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'wishlist_products.xml';
        $content = $this->_view->getLayout()->createBlock(
            \Mangoit\ReportsPatch\Block\Adminhtml\Wishlist\Grid::class
        )->getExcelFile(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
