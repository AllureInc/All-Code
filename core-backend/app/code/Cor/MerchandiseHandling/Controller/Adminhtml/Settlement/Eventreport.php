<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Controller File
 * Generates settlement report according to event and artist.
 */
namespace Cor\MerchandiseHandling\Controller\Adminhtml\Settlement;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
/**
 * Main controller class
 */
class Eventreport extends Action
{   
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Total Apparel Tax
     */
    protected $_totalApparelTax;

    /**
     * @var Total Music Tax
     */
    protected $_totalMusicTax;

    /**
     * @var Total Other Tax
     */
    protected $_totalOtherTax;

    /**
     * @var Total Of All Taxes
     */
    protected $_totalOfTax;

    /**
     * @var Total Sold Products
     */
    protected $_totalSoldProduct;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
 
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
 
    }

    /**
     * create pdf report
     */
    public function execute()
    {
        $event_id = $this->getRequest()->getParam('event_id');
        $artist_id = $this->getRequest()->getParam('artist_id', 0);
        $resultPage = $this->_resultPageFactory->create();

        $html = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Cor_MerchandiseHandling::settlement/report/event_pdf.phtml')
            ->setEventId($event_id)
            ->setArtistId($artist_id)
            ->toHtml();

        $helper = $this->_objectManager->create('Cor\MerchandiseHandling\Helper\Data');
        $logger = $helper->getLogger(); //logger

        $eventData = $helper->getEventDetails($event_id);

        $directory = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $directoryPath  =  $directory->getPath('media');
        $temp = $directoryPath.'/temp';
        $filepath = $directoryPath.'/settlement/report/';

        $eventName = $eventData->getEventName();
        $fileName = $eventName.' Settlement Report.pdf';
        $excelFiles = false;

        if ($artist_id) {
            $artistData = $helper->getArtistDetails($artist_id);
            $artistName = $artistData->getArtistName();
            $fileName = $artistName.' - '.$fileName;

            $excelFiles = $this->getExcelFile($artistName, $eventName, $eventData, $artist_id, $event_id, $logger);

            $updateData = array('id' => $artist_id, 'most_recent_settlement_date' => date('Y-m-d'));
            $helper->setArtistDetails($artist_id, $updateData);
        }

        $mpdf = new \Mpdf\Mpdf(['tempDir' => $temp]);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);
        $mpdf->Output($filepath.$fileName, 'F');
        
        if ($excelFiles) {
            $result = array(
                'response' => true,
                'file'=> $fileName,
                'mastersheet'=> $excelFiles['mastersheet'],
                'taxsheet'=> $excelFiles['taxsheet']
            );
        } else {
            $result = array(
                'response' => true,
                'file'=> $fileName
            );
        }
        echo json_encode($result);
        exit();
    }

    /*
    * Generate excel reports
    * @return boolean|array
    */
    public function getExcelFile($artistName, $eventName, $eventData, $artist_id, $event_id, $logger)
    {
        $masterFileHeader = array('','Price','Count In','Add','Total In','Comp','Count Out','Total Sold','Gross'); 
        $taxFileHeader = $this->getTaxCategoryHeaders();
        $logger->info('');

        $this->_totalSoldProduct = 0;
        $this->_totalApparelTax = 0;
        $this->_totalMusicTax = 0;
        $this->_totalOtherTax = 0;
        $this->_totalOfTax = 0;
        $total_tax_category = [];
        $totalTaxDataHeaders = [];

        $blankArray = array(" ");
        /* Directory and filepath */
        $directory = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $directoryPath  =  $directory->getPath('media');
        $filepath = $directoryPath.'/settlement/report/excelreport/';    
        /* Directory and filepath ends */

        /* Helpers and collections */
        $helper = $this->_objectManager->create('Cor\MerchandiseHandling\Helper\Exceldata');
        $productCollection = $helper->getProductsData($artist_id, $event_id);   
        /* Helpers and collections ends*/
        
        if (!empty($productCollection)) {
            $eventAddress = $eventData['event_street'].' '.$eventData['event_city'].' '.$eventData['event_state'].' '.$eventData['event_country'];
            $first_date = date_create($eventData['start_date']);
            $start_date = date_format($first_date,"d-m-y");
            $second_date = date_create($eventData['end_date']);
            $end_date = date_format($second_date,"d-m-y");
            $eventDate = $start_date." to ".$end_date;
            /* Report files name*/
            $fileName = $artistName.', '.$eventName.' - Settlement Report.csv';
            $second_report = $artistName.', '.$eventName.' - Tax Data Report.csv';
            $tax_data = fopen($filepath.$second_report,"w");
            $file = fopen($filepath.$fileName,"w");   
            /* Event details */   
            $header = array
            (
                "".$eventData['event_name'],
                "".$eventAddress,
                "".$eventDate,
                "".$artistName,
                "",
            );
            foreach ($header as $line)
            {
                fputcsv($file,explode(',',$line));
                fputcsv($tax_data,explode(',',$line));
            }

            /* write data for simple product */   
            if (isset($productCollection['simple'])) {
                fputcsv($file, $masterFileHeader);
                fputcsv($tax_data, $taxFileHeader);
                $simple = 'simple';
                $logger->info('Total Sale of simpe products');
                $total_sold_product = $this->setProductData($productCollection['simple'], $simple, $file, $tax_data,$helper, $logger);
                $this->getTotalsOfRecords($total_sold_product);
            }

            /* write data for configurable product */
            if (isset($productCollection['configurable'])) {
                $logger->info('Total Sale of configurable products');
                foreach ($productCollection['configurable'] as $key => $value) {
                    $parent_product_name = array("","".$key);
                    foreach ($parent_product_name as $parent) {
                        fputcsv($file, explode(',', $parent));
                        fputcsv($tax_data, explode(',', $parent));
                    }
                    $productType = 'configurable';
                    $total_sold_product = $this->setProductData($value, $productType, $file, $tax_data,$helper, $logger);
                    $this->getTotalsOfRecords($total_sold_product);
                }
            }
            
            /* write data for bundle product */
            if (isset($productCollection['bundle'])) {
                $logger->info('Total Sale of bundle products');
                foreach ($productCollection['bundle'] as $key => $value) {
                    $parent_product_name = array("","".$key);
                    foreach ($parent_product_name as $parent) {
                        fputcsv($file, explode(',', $parent));
                        fputcsv($tax_data, explode(',', $parent));
                    }

                    fputcsv($file,$masterFileHeader); 
                    fputcsv($tax_data,$taxFileHeader);
                    $productType = 'bundle';
                    $total_sold_product =  $this->setProductData($value, $productType, $file, $tax_data,$helper, $logger);   
                    $this->getTotalsOfRecords($total_sold_product);
                }
            }

            /* write data for grouped product */
            if (isset($productCollection['grouped'])) {
                $logger->info('Total Sale of group products');
                foreach ($productCollection['grouped'] as $key => $value) {
                    $parent_product_name = array("","".$key);
                    foreach ($parent_product_name as $parent) {
                        fputcsv($file, explode(',', $parent));
                        fputcsv($tax_data, explode(',', $parent));
                    }             
                    fputcsv($file, $masterFileHeader); 
                    fputcsv($tax_data,$taxFileHeader); 
                    $productType = 'grouped';
                    $total_sold_product = $this->setProductData($value, $productType, $file, $tax_data,$helper, $logger);  
                    $this->getTotalsOfRecords($total_sold_product);
                }
            }

            /* write data for virtual product */
            if (isset($productCollection['virtual'])) {
                $logger->info('Total Sale of virtual products');
                fputcsv($file,$masterFileHeader);
                fputcsv($tax_data, $taxFileHeader);
                $productType = 'virtual';
                $total_sold_product = $this->setProductData($productCollection['virtual'], $productType, $file, $tax_data,$helper, $logger);
                $this->getTotalsOfRecords($total_sold_product);
            }
            $totalSoldProduct = array('','','','','','Total','', ''.$this->_totalSoldProduct); 

            $categoryArrayData = $helper->getCategoriesIds();
            $total_tax_category[$categoryArrayData['apparel']]  = $this->_totalApparelTax;
            $total_tax_category[$categoryArrayData['music']]  = $this->_totalMusicTax;
            $total_tax_category[$categoryArrayData['other']]  = $this->_totalOtherTax;
            $totalTaxData[] = 'TOTALS'; 
            $totalTaxData[] = ''; 
            $totalTaxData[] = ''.$this->_totalSoldProduct; 
            $totalTaxData[] = ''; 
            $totalTaxData[] = ''; 
            $totalTaxData = $helper->getTotalOfCategories($totalTaxData, $total_tax_category);
            $logger->info('');            
            $logger->info('overall total sold product '.$this->_totalSoldProduct);            
            $totalTaxData[] = $this->_totalOfTax;
            $totalTaxDataHeaders[] = '';
            $totalTaxDataHeaders[] = '';
            $totalTaxDataHeaders[] = 'Total Sold';
            $totalTaxDataHeaders[] = '';
            $totalTaxDataHeaders[] = '';
            $totalTaxDataHeaders = $this->getTotalsHeaders($totalTaxDataHeaders);
            $totalTaxDataHeaders[] = 'Total Tax';
             array('',''.$this->_totalSoldProduct,'','',);

            fputcsv($file, $totalSoldProduct);
            fputcsv($tax_data, $totalTaxDataHeaders);
            fputcsv($tax_data, $totalTaxData);
            fclose($file);
            fclose($tax_data);
            $logger->info('--End of the settlement report of '.$artistName.'.--'); 
            return array('mastersheet'=> $fileName, 'taxsheet'=> $second_report);            
        } else {
            return false;
        }
    }

    /**
    * Method for write product data and product total
    * @return integer
    */
    public function setProductData($data, $productType, $file, $tax_data, $helper, $logger)
    {
        $headerArray = array('Price', 'Count In', 'Add', 'Total In', 'Comp', 'Count Out', 'Total Sold', 'Gross');
        $categoryArray = $helper->getCategoriesIds();
        $tax_data_array = array();
        $totalRow = array();
        $cTotalAddOn = 0;
        $cTotal = 0;
        $cComp = 0;
        $cCountOut = 0;
        $cTotalSold = 0;
        $cGrosssale = 0;
        $apparelTax = 0;
        $otherTax = 0;
        $musicTax = 0;
        $totalTax = 0;
        $totalRowTax = [];
        $total_category_tax = [];
        foreach ($data as $item) {
            if (isset($item['add_on'])) {
                $cTotalAddOn = $cTotalAddOn + (int)$item['add_on'];
                $cTotal = $cTotal + (int)$item['total'];
                $cComp = $cComp + (int)$item['comp'];
                $cCountOut = $cCountOut + (int)$item['count_out'];
                $cTotalSold = $cTotalSold + (int)$item['total_sold'];
                $cGrosssale = $cGrosssale + (int)$item['gross_sale'];
                $masterData = $this->getMasterData($item);
                fputcsv($file, $masterData);
                /* Removing array keys for taxdata report */
                $taxData = $this->getTaxData($item);
                fputcsv($tax_data, $taxData);
                    /* For Tax Total */ 
                    $apparelTax = $apparelTax + $taxData[$categoryArray['apparel']];
                    $musicTax = $musicTax + $taxData[$categoryArray['music']];
                    $otherTax = $otherTax + $taxData[$categoryArray['other']];
                    $totalTax = $totalTax + $taxData['total_tax'];
            } else {
                if (isset($item['tax_data'])) {
                    fputcsv($tax_data, $item['tax_data']);           
                } else{
                    fputcsv($file, $item);                     
                }
            }

        }
        /* For blank grids in total */ 
        if ($productType == 'configurable') {
            foreach ($data[0] as $key) {
                /* For searching attributes of configurable products */ 
                if (!in_array($key, $headerArray)) {
                    $totalRow[] = ''; 
                    $totalRowTax[] = '';                   
                }
            }
        } else {
            $totalRow[] = '';
            $totalRowTax[] = '';
        }
        /* For Product Total */ 
        $totalRow[] = 'Total';
        $totalRow[] = '';
        $totalRow[] = $cTotalAddOn;
        $totalRow[] = $cTotal;
        $totalRow[] = $cComp;
        $totalRow[] = $cCountOut;
        $totalRow[] = $cTotalSold;
        $totalRow[] = $cGrosssale;
        /* For Tax Total */ 
        $totalRowTax[] = 'Total';
        $totalRowTax[] = $cTotalSold;
        $totalRowTax[] = $cGrosssale;
        if (($productType != 'simple') && ($productType != 'virtual')) {
            $totalRowTax[] = $taxData['tax_rate'];
        } else {
            $totalRowTax[] = '';
        }
        $total_category_tax[$categoryArray['apparel']] = $apparelTax;
        $total_category_tax[$categoryArray['music']] = $musicTax;
        $total_category_tax[$categoryArray['other']] = $otherTax;
        $totalRowTax = $helper->getTotalOfCategories($totalRowTax, $total_category_tax);
        $totalRowTax[] = $totalTax;

        fputcsv($tax_data, $totalRowTax);
        unset($totalRowTax);
        fputcsv($file, $totalRow);
        unset($totalRow);

        $blankArray = array();
        fputcsv($file,$blankArray);   
        fputcsv($tax_data,$blankArray);
        /* Logger */    
        $logger->info('');
        $logger->info($productType.' Apparel Tax Amount '.$apparelTax);
        $logger->info($productType.' Music Tax Amount '.$musicTax);
        $logger->info($productType.' Other Tax Amount '.$otherTax);
        $logger->info($productType.' Total Tax Amount '.$totalTax);
        $returnResult = array(
            'master_tbl_total_sold'=> $cTotalSold,
            'tax_tbl_apparel_total'=> $apparelTax,
            'tax_tbl_music_total'=> $musicTax,
            'tax_tbl_other_total'=> $otherTax,
            'tax_tbl_total_tax'=> $totalTax
            );
        return $returnResult;
    }

    /**
     * Method for generates array of master sheet's data
     * @return array
     */
    public function getMasterData($item)
    {
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();
        $removeKey[] = 'tax_rate';
        $removeKey[] = 'total_tax';
        foreach ($category->getData() as $data) {
            $removeKey[] = $data['id'];            
        }
        foreach ($removeKey as $key) {
            if (isset($item[$key])) {
                unset($item[$key]);
            }
        }
        return $item;
    }

    /**
     * Method for generates array of tax sheet's data
     * @return array
     */
    public function getTaxData($item)
    {
        $removeKey = array('count_in', 'add_on', 'total', 'comp', 'count_out');
        foreach ($removeKey as $key) {
            unset($item[$key]);
        }
        return $item;
    }

    /**
     * Method for generates array of tax sheet's headers
     * @return array
     */
    public function getTaxCategoryHeaders()
    {
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();
        $taxFileHeader[] = '';
        $taxFileHeader[] = 'Price';
        $taxFileHeader[] = 'Total Sold';
        $taxFileHeader[] = 'Gross';
        $taxFileHeader[] = 'Tax Rate';
        foreach ($category->getData() as $categorydata) {
            $taxFileHeader[] = ''.$categorydata['category_name'];
        }
        $taxFileHeader[] = 'Total Tax';
        return $taxFileHeader;
    }

    /**
     * Method for retrive Total sold product, Total apparel Tax, Total Music Tax, Total Other Tax, Total Of Tax
     * @return array
     */
    public function getTotalsOfRecords($total_sold_product)
    {
        $this->_totalSoldProduct =  $this->_totalSoldProduct + $total_sold_product['master_tbl_total_sold'];
        $this->_totalApparelTax =  $this->_totalApparelTax + $total_sold_product['tax_tbl_apparel_total'];
        $this->_totalMusicTax =  $this->_totalMusicTax + $total_sold_product['tax_tbl_music_total'];
        $this->_totalOtherTax =  $this->_totalOtherTax + $total_sold_product['tax_tbl_other_total'];
        $this->_totalOfTax =  $this->_totalOfTax + $total_sold_product['tax_tbl_total_tax'];
    }

    /**
     * Method for retrive headers of Total apparel Tax, Total Music Tax, Total Other Tax, Total Of Tax
     * @return array
     */
    public function getTotalsHeaders($totalTaxDataHeaders)
    {
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();
        foreach ($category->getData() as $item) {
            $totalTaxDataHeaders[] = $item['category_name'];
        }
        return $totalTaxDataHeaders;
    }
}
