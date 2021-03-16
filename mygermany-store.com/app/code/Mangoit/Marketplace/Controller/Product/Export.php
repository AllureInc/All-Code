<?php

namespace Mangoit\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mangoit\Marketplace\Helper\DefaultExportProductData as DefaultCsvData;

class Export extends \Webkul\MpMassUpload\Controller\Product\Export
{
     /**
     * @var RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var WriteFactory
     */
    protected $_filewrite;
    protected $scopeConfig;
    protected $logger;
    protected $_defaultCsvData;

    /**
     * @param Context $context,
     * @param PageFactory $resultPageFactory,
     * @param \Magento\Customer\Model\Url $url,
     * @param \Magento\Customer\Model\Session $session,
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
     * @param \Webkul\MpMassUpload\Helper\Export $helperExport,
     * @param \Webkul\Marketplace\Model\Product $mpProduct,
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper,
     * @param AdapterCsv $writer,
     * @param FileFactory $fileFactory,
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Webkul\MpMassUpload\Helper\Export $helperExport,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        AdapterCsv $writer,
        FileFactory $fileFactory,
        WriteFactory $writeFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        DefaultCsvData $defaultCsvData
    ) {
        $this->_resultRawFactory = $resultRawFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_filewrite = $writeFactory->create(BP);
        $this->logger = $logger;
        $this->_defaultCsvData = $defaultCsvData;
        parent::__construct(            
            $context,
            $resultPageFactory,
            $url,
            $session,
            $massUploadHelper,
            $helperExport,
            $mpProduct,
            $marketplaceHelper,
            $writer,
            $fileFactory
        );
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $mphelper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $isPartner = $mphelper->isSeller();
        if ($isPartner == 1) {
            $productType = $this->getRequest()->getParam('type');
            $allowedTypes = ['csv', 'xml', 'xls'];
            if (isset($productType) &&  (in_array($productType, $allowedTypes))) {

                $catalogProductEntity = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
                )->getTable('catalog_product_entity');

                $catalogProductEntityInt = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
                )->getTable('catalog_product_entity_int');

                $storeId = $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Data'
                )->getCurrentStoreId();

                $eavAttribute = $this->_objectManager->get(
                    'Magento\Eav\Model\ResourceModel\Entity\Attribute'
                );
                $deliveryDaysFrom = $eavAttribute->getIdByCode('catalog_product', 'delivery_days_from');
                $deliveryDaysTo = $eavAttribute->getIdByCode('catalog_product', 'delivery_days_to');

                try {
                    $helper = $this->_massUploadHelper;
                    $sellerId = $this->marketplaceHelper->getCustomerId();
                    $data = $this->getRequest()->getParams();
                    $productsRow = [];
                    if (empty($data['product_type'])) {
                        $this->messageManager->addError(
                            __("Product type should be selected.")
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/export',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }

                    $productType = $data['product_type'];
                    $allowedAttributes = [];
                    if (!empty($data['custom_attributes'])) {
                        $allowedAttributes = $data['custom_attributes'];
                    }
                    $allowedAttributes = ['delivery_days_from', 'delivery_days_to'];
                    $sensitiveAttrs = $this->_objectManager->create('Mangoit\Marketplace\Helper\Data')->getSensitiveAttributes();
                    if (!empty($sensitiveAttrs)){
                        foreach ($sensitiveAttrs as $sensAttrValues){
                            $allowedAttributes[] = $sensAttrValues->getAttributeCode();
                        }
                    }
                    // $fileName = $productType.'_product.csv';
                    $products = $this->_mpProduct
                        ->getCollection()
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                    $products->getSelect()->join(
                        $catalogProductEntity.' as cpe',
                        'main_table.mageproduct_id = cpe.entity_id',
                        'type_id'
                    );

                    $products->getSelect()->join(
                        $catalogProductEntityInt.' as product_entity_int',
                        'main_table.mageproduct_id = product_entity_int.entity_id AND product_entity_int.attribute_id='.$deliveryDaysFrom,
                        ['delivery_days_from' => 'value']
                    );

                    $products->getSelect()->join(
                        $catalogProductEntityInt.' as product_entity_int_new',
                        'main_table.mageproduct_id = product_entity_int_new.entity_id AND product_entity_int_new.attribute_id='.$deliveryDaysTo,
                        ['delivery_days_to' => 'value']
                    );

                    $products->getSelect()->join(
                        $catalogProductEntityInt.' as cpev',
                        'main_table.mageproduct_id = cpev.entity_id'
                    )->where(
                        'cpev.attribute_id = '.$deliveryDaysFrom
                    );
                    $products->addFieldToFilter('type_id', $productType);
                    $products->getSelect()->limit(4);
                    // echo "<pre>";
                    // // echo $products->getSelect();
                    // print_r($products->getData());
                    // die('died');
                    foreach ($products as $value) {
                        $productIds[] = $value->getMageproductId();
                    }
                    // $productIds = $products->getAllIds();
                    if (!empty($productIds)) {
                        $productsRow = $this->_helperExport->exportProducts(
                            $productType,
                            $productIds,
                            $allowedAttributes
                        );
                    } else {
                        $productsRow = $this->getDefualtCsvData($productType);
                    }
                    $this->logger->info("##### MassUpload #####");
                    $this->logger->info(json_encode($productsRow));
                    $this->logger->info("##### MassUpload Ends #####");

                    if (count($productsRow)) {
                        /*
                        * Custom code added for all file formats.
                        */
                        $configDataRow = $productsRow;
                        $electronicTypeVal = $this->scopeConfig->getValue('marketplace/product_cat_type/electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $nonElectronicTypeVal = $this->scopeConfig->getValue('marketplace/product_cat_type/non_electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        foreach ($configDataRow[1] as &$dataRow) {
                            if (!empty($dataRow) && isset($dataRow['product_cat_type'])) {
                                if ($dataRow['product_cat_type'] == $electronicTypeVal) {
                                    $dataRow['product_cat_type'] = 'Electronics';
                                }elseif ($dataRow['product_cat_type'] == $nonElectronicTypeVal) {
                                    $dataRow['product_cat_type'] = 'Non-Electronics';
                                }
                            }
                        }
                        $fileContents = $fileName = $fileTyp = '';
                        if($data['type'] == "csv") {
                            $writer = $this->_writer;
                            $writer->setHeaderCols($configDataRow[0]);
                            foreach ($configDataRow[1] as $dataRow) {
                                if (!empty($dataRow)) {
                                    $writer->writeRow($dataRow);
                                }
                            }
                            $fileTyp = 'text/csv';
                            $fileName = $productType.'_product.csv';
                            $fileContents = $writer->getContents();

                        } else if ($data['type'] == "JSON") {
    
                            $fileTyp = 'text/json';
                            $fileName = $productType.'_product.json';
                            $fileContents = json_encode($configDataRow[1], JSON_PRETTY_PRINT);

                        } else if ($data['type'] == "xls") {
                            // die('died 182');
                            $spreadsheet = new Spreadsheet();
                           // Set document properties
                            $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
                                ->setLastModifiedBy('Maarten Balliauw')
                                ->setTitle('Office 2007 XLSX Test Document')
                                ->setSubject('Office 2007 XLSX Test Document')
                                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                                ->setKeywords('office 2007 openxml php')
                                ->setCategory('Test result file');
                            if ($productType == 'configurable') {
                                $newHeader = isset($configDataRow[1][0]) ? array_keys($configDataRow[1][0]) : $configDataRow[0];
                            } else {
                                $newHeader = isset($configDataRow[1][1]) ? array_keys($configDataRow[1][1]) : $configDataRow[0];
                            }

                            // Add some data
                            $spreadsheet->getActiveSheet()
                            ->fromArray(
                                $newHeader,  // The data to set
                                NULL,        // Array values with this value will not be set
                                'A1'         // Top left coordinate of the worksheet range where
                                             //    we want to set these values (default is A1)
                            );
                            $spreadsheet->getActiveSheet()
                            ->fromArray(
                                $configDataRow[1],  // The data to set
                                NULL,        // Array values with this value will not be set
                                'A2'         // Top left coordinate of the worksheet range where
                                             //    we want to set these values (default is A1)
                            );
                            // // Rename worksheet
                            $spreadsheet->getActiveSheet()->setTitle($productType);
                            $spreadsheet->getSheetByName('SHEET NAME');
                            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                            $spreadsheet->setActiveSheetIndex(0);
                            $fileName = $productType.'_product.xlsx';
                            // Redirect output to a client’s web browser (Xlsx)
                            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                            header('Content-Disposition: attachment;filename="'.$fileName.'"');
                            header('Cache-Control: max-age=0');
                            // If you're serving to IE 9, then the following may be needed
                            header('Cache-Control: max-age=1');
                            // If you're serving to IE over SSL, then the following may be needed
                            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                            header('Pragma: public'); // HTTP/1.0
                            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                            $writer->save('php://output');
                            exit;
                            // $data = array_map(function($v){
                            //         return (is_null($v)) ? " " : $v;
                            //     }, $configDataRow[1]);
                            // $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));

                            // $convert->setDataHeader($configDataRow[0]);

                            // $fileTyp = ''; //'text/json';
                            // $fileName = $productType.'_product.xlsx';
                            // $fileContents = $convert->convert('single_sheet');

                            // $fileContents = str_replace(
                            //         '</Workbook>',
                            //         '<Styles></Styles><Styles></Styles></Workbook>',
                            //         $fileContents
                            //     );
                        } else if ($data['type'] == "xml") {
                            $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><node></node>");

                            // Call arrayToXml that will convert the array to xml string.
                            $this->_arrayToXml($configDataRow[1], $xml);

                            $fileTyp = '';
                            $fileName = $productType.'_product.xml';
                            $fileContents = $xml->asXML();
                        }

                        if ($fileContents != '' && $fileName != '') {
                            // $this->fileFactory->create(
                            //     $fileName,
                            //     null, //content here. it can be null and set later 
                            //     DirectoryList::VAR_DIR,
                            //     $fileTyp //content type here
                            // );
                            // $resultRaw = $this->_resultRawFactory->create();
                            // $resultRaw->setContents($fileContents); //set content for download file here
                            // return $resultRaw;

                            return $this->fileFactory->create(
                                $fileName,
                                $fileContents,//null, //content here. it can be null and set later 
                                DirectoryList::VAR_DIR,
                                $fileTyp //content type here
                            );
                        }
                        /**/
                    } else {
                        $this->messageManager->addError(
                            __("There is no product with product type: %1 to export.", $productType)
                        );
                    }
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/product/view',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/product/view',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $resultPage = $this->_resultPageFactory->create();
                if ($this->marketplaceHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mpmassupload_layout2_product_export');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Marketplace MassUpload Product Export')
                );
                return $resultPage;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /*
     * function defination to convert array to xml
     */
    protected function _arrayToXml($data, &$xml ) {
        foreach( $data as $key => $value ) {
            // if( is_numeric($key) ){
            //     $key = 'item'.$key; //dealing with <0/>..<n/> issues
            // }
            if( is_array($value) ) {
                $childNode = $xml->addChild('product');
                self::_arrayToXml($value, $childNode);
            } else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    public function getDefualtCsvData($productType)
    {
        if ($productType == 'simple') {
            return $this->_defaultCsvData->getSimpleProductData();
        } else {
            return $this->_defaultCsvData->getConfigurableProductData();
        }

    }
}