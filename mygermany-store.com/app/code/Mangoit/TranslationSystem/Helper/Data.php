<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_TranslationSystem
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Solutions Private Limited
 */

namespace Mangoit\TranslationSystem\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\App\ObjectManager;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AdapterCsv
     */
    protected $_writer;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;

    protected $productFaq;
    protected $_sellerCollectionFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    protected $directoryList;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AdapterCsv $writer
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\File\Csv $csvReader
     *
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AdapterCsv $writer,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\File\Csv $csvReader,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        PreorderSellerCollection $preorderSellerCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_writer = $writer;
        $this->_fileUploader = $fileUploaderFactory;
        $this->_csvReader = $csvReader;
        $this->productFaq = $productFaq;
        $this->_sellerCollectionFactory = $preorderSellerCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    public function getTranslationContent() {
        $mphelper = $this->marketplaceHelper;
        $isPartner = $mphelper->isSeller();
        $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();
        $faqCollection = $this->productFaq->getCollection()
        ->addFieldToFilter('vendor_id', $mphelper->getCustomerId())
        ->addFieldToFilter('store_id', $storeId);
        $partner = array();
        $wordcount = 0;
        $translationData = $contentArray = [];
        if ($isPartner == 1) {
            $partner = $this->getSellerForTranslation($mphelper->getCustomerId());
            // $partner = $mphelper->getSeller();

            $contentArray['shop_title'] = strip_tags(
                isset($partner['shop_title'])?$partner['shop_title']:'');
            $contentArray['company_locality'] = strip_tags(
                isset($partner['company_locality'])?$partner['company_locality']:'');
            $contentArray['company_description'] = strip_tags(
                isset($partner['company_description'])?$partner['company_description']:'');
            $contentArray['meta_keyword'] = strip_tags(
                isset($partner['meta_keyword'])?$partner['meta_keyword']:'');
            $contentArray['meta_description'] = strip_tags(
                isset($partner['meta_description'])?$partner['meta_description']:'');
            $contentArray['return_policy'] = strip_tags(
                isset($partner['return_policy'])?$partner['return_policy']:'');
            $contentArray['shipping_policy'] = strip_tags(
                isset($partner['shipping_policy'])?$partner['shipping_policy']:'');

            // $wordcount = str_word_count(strip_tags(implode(' ', $contentArray)))+1;
            $sellerId = $this->marketplaceHelper->getCustomerId();
            $storeCode = $this->_storeManager->getDefaultStoreView()->getCode();
            $returnArr = $this->_sellerCollectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->getFirstItem()
                ->getData();

            if(isset($returnArr['custom_message'])){
                $customMsgs = unserialize($returnArr['custom_message']);
                $contentArray['preorder_msg'] = isset($customMsgs[$storeCode]) ? $customMsgs[$storeCode] : $customMsgs[0];
            }


            $translationData['shop'] = $contentArray;

            $sellerProducts = $this->_sellerProductCollectionFactory
                    ->create()
                    ->addFieldToSelect('mageproduct_id')
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )->getData();

            $productIds = array_column($sellerProducts, 'mageproduct_id');

            $mageProducts = $this->_productCollectionFactory
                    ->create()
                    ->addAttributeToSelect('*')
                    ->addStoreFilter($this->_storeManager->getDefaultStoreView())
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $productIds]
                    );

            foreach ($mageProducts as $mageProduct) {
                $productContent[$mageProduct->getId()]['name'] = strip_tags($mageProduct->getName());
                $productContent[$mageProduct->getId()]['meta_title'] = strip_tags($mageProduct->getMetaTitle());
                $productContent[$mageProduct->getId()]['meta_description'] = strip_tags($mageProduct->getMetaDescription());
                $productContent[$mageProduct->getId()]['description'] = strip_tags($mageProduct->getDescription());
                $productContent[$mageProduct->getId()]['short_description'] = strip_tags($mageProduct->getShortDescription());
                $productContent[$mageProduct->getId()]['meta_keyword'] = strip_tags($mageProduct->getMetaKeyword());
                $translationData['product'] = $productContent;
            }

            foreach ($faqCollection as $faqData) {
                $FaqContent[$faqData->getId()]['title'] = strip_tags($faqData->getTitle());
                $FaqContent[$faqData->getId()]['description'] = strip_tags($faqData->getDescription());
                $translationData['faqs'] = $FaqContent;
            }

        }
        return $translationData;
    }

    public function getSellerForTranslation($sellerId)
    {
        $data = [];
        $defaultStoreId = $this->_storeManager->getDefaultStoreView()->getId();
        $model = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $sellerId)
        ->addFieldToFilter('store_id', $defaultStoreId);
        // If seller data doesn't exist for current store
        // if (!count($model)) {
        //     $model = $this->_objectManager->create(
        //         'Webkul\Marketplace\Model\Seller'
        //     )->getCollection()
        //     ->addFieldToFilter('seller_id', $sellerId)
        //     ->addFieldToFilter('store_id', 0);
        // }
        foreach ($model as $value) {
            $data = $value->getData();
        }
        return $data;
    }

    public function getAttachmentContent(){
        $translateRow = $fieldRow = [];
        $translateRow[0] = $this->prepareFileColumnRow();
        $translationData = $this->getTranslationContent();
        $defaultStoreName = $this->_storeManager->getDefaultStoreView()->getName();
        $defaultStoreCode = $this->_storeManager->getDefaultStoreView()->getCode();
        $columnsKey = $defaultStoreName.' ('.$defaultStoreCode.')';

        foreach ($translationData as $contentTyp => $contentArray) {
            foreach ($contentArray as $idKey => $contentVal) {
                if(is_array($contentVal)) {
                    foreach ($contentVal as $valKey => $contentData) {
                        $field = [];
                        $field['Unique Identifier'] = $contentTyp.'-'.$idKey.'-'.$valKey;
                        $field[$columnsKey] = $this->filterTranslateContent($contentData);
                        $fieldRow[] = $field;
                    }
                } else {
                    $field = [];
                    $field['Unique Identifier'] = $contentTyp.'-'.$idKey;
                    $field[$columnsKey] = $this->filterTranslateContent($contentVal);
                    $fieldRow[] = $field;
                }
            }
        }
        $translateRow[1] = $fieldRow;

        $attachmentContent = [];
        $attachmentContent['csv_content'] = $this->getCsvContent($translateRow);
        $attachmentContent['pdf_content'] = $this->getPdfContent($translateRow);
        // print_r($attachmentContent);
        return $attachmentContent;
    }

    private function getCsvContent($dataArray = [])
    {
        if (count($dataArray)) {
            $writer = $this->_writer;
            $writer->setHeaderCols($dataArray[0]);
            foreach ($dataArray[1] as $dataRow) {
                if (!empty($dataRow)) {
                    $writer->writeRow($dataRow);
                }
            }
            return $writer->getContents();
        }
    }

    private function getPdfContent($dataArray = [])
    {
        $pdfHtml = $this->getPdfHtml($dataArray);
        $mediaDir = $this->directoryList->getPath('media');
        $mpdf = new \Mpdf\Mpdf(['tempDir' => $mediaDir . 'catalog/tmp']);
        // $mpdf->autoPageBreak = true;
        $mpdf->shrink_tables_to_fit = 1;

        $mpdf->WriteHTML($pdfHtml);
        // $mpdf->SetDisplayMode('fullpage');
        // $mpdf->output('/var/www/html/mygermanystaging/test.pdf', "F");
        $pdfString = $mpdf->output('', "S");

        return $pdfString;
    }

    private function getPdfHtml($dataArray = [])
    {
        $om = ObjectManager::getInstance();
        $imageObj = $om->get('\Magento\Theme\Block\Html\Header\Logo');
        $image = $imageObj->getLogoSrc();

        $tableHeads = '';
        $tableContent = '';
        // $widthPerColumn = (2480/count($dataArray[0]));
        $dummyColumns =  array_map(function ($value) { return null; }, $dataArray[0]);
        $i = 0;
        foreach ($dataArray[0] as $headColumn) {
            $tableHeads .= '<th style="text-align:left;font-size:22px;"><span>'.__($headColumn).'</span></th>';
            if($i == 1){
                break;
            }
            $i++;
        }

        // $k = 0;
        // foreach ($dataArray[1] as $perRow) {
        //     // $rowData = array_merge($perRow, array_values($dummyColumns));
        //     $tableContent .= '<tr>';
        //     $j = 0;
        //     foreach ($perRow as $row) {
        //         $widthPerColumn = ($j == 0) ? 30 : 70;
        //         $tableContent .= '<td style="font-size:20px;width:'.$widthPerColumn.'%;"><span>'.$row.'</span></td>';
        //         $j++;
        //     }
        //     $tableContent .= '</tr>';
        //     if($k > 35) {
        //         // $tableContent .= '<pagebreak></pagebreak>';
        //         // break;
        //     }
        //     $k++;
        // }

        $dataToPdf = array_chunk($dataArray[1], 28);
        $tableHtml = '';
        foreach ($dataToPdf as $pdfData) {
            $tableContent = '';
            foreach ($pdfData as $perRow) {
                $tableContent .= '<tr>';
                $j = 0;
                foreach ($perRow as $row) {
                    $widthPerColumn = ($j == 0) ? 38 : 58;
                    $tableContent .= '<td style="font-size:20px;width:'.$widthPerColumn.'%;"><span>'.$row.'</span></td>';
                    $j++;
                }
                $tableContent .= '</tr>';
            }
            $tableHtml .= '
                <table width="100%" style="page-break-inside: avoid;">
                    <thead>
                        <tr>'.$tableHeads.'</tr>
                    </thead>
                    <tbody>'.$tableContent.'</tbody>
                </table>';
        }

        $finalHtml = '
            <!DOCTYPE html>
            <html>
                <head>
                    <title>'.__('Translation Content').'</title>
                </head>
                <body>
                    <table width="100%" style="page-break-inside: avoid;">
                        <tr>
                            <td style="text-align: left;">
                                <span style="width:10px;">
                                    <img width="300" src="' . $image . '" style="max-width:100%;"/>
                                </span><br/>
                            </td>
                        </tr>
                        <tr>
                           <td><br/></td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-family:Arial;font-size:20px;color:#4d4843">
                                    <strong>'.__('Translation Content').'</strong>
                                </span>
                            </td>
                        </tr>
                    </table>
                    '.$tableHtml.'
                </body>
            </html>';

        return $finalHtml;
    }

    public function prepareFileColumnRow()
    {
        $storeManagerDataList = $this->_storeManager->getStores();
        $columns = ['Unique Identifier'];

        $defaultStoreName = $this->_storeManager->getDefaultStoreView()->getName();
        $defaultStoreCode = $this->_storeManager->getDefaultStoreView()->getCode();
        $columns[$defaultStoreCode] = $defaultStoreName.' ('.$defaultStoreCode.')';


        foreach ($storeManagerDataList as $key => $value) {
            if(!array_key_exists($value['code'], $columns)){
                $columns[$value['code']] = $value['name'].' ('.$value['code'].')';
            }
        }
        return $columns;
    }

    public function getStoreIds()
    {
        $storeManagerDataList = $this->_storeManager->getStores();

        foreach ($storeManagerDataList as $key => $value) {
            $columns[$value['name'].$value['code']] = $value['store_id'];
        }
        return $columns;
    }

    public function getStoreCodes()
    {
        $storeManagerDataList = $this->_storeManager->getStores();

        foreach ($storeManagerDataList as $key => $value) {
            $columns[$value['name'].$value['code']] = $value['code'];
        }
        return $columns;
    }

    private function filterTranslateContent($translateContent = '')
    {
        return trim(preg_replace('/\s+/', ' ', $translateContent));
    }

    /**
     * Validate Uploaded Files
     *
     * @return array
     */
    public function validateUploadedFiles()
    {
        $validateCsv = $this->validateCsv();
        if ($validateCsv['error']) {
            return $validateCsv;
        }
        $csvFile = $validateCsv['csv'];

        // End: Calculate Profile Mapped Attribute Data Array
        $csvFilePath = $validateCsv['path'];
        $uploadedFileRowData = $this->readCsv($csvFilePath);
        $result = [
            'error' => false,
            'csv' => $csvFile,
            'csv_data' => $uploadedFileRowData,
            'extension' => $validateCsv['extension']
        ];
        return $result;
    }

    /**
     * Validate uploaded Csv File
     *
     * @return array
     */
    public function validateCsv()
    {
        try {
            $csvUploader = $this->_fileUploader->create(['fileId' => 'upload_csv']);
            $csvUploader->setAllowedExtensions(['csv']);
            $validateData = $csvUploader->validateFile();
            $extension = $csvUploader->getFileExtension();
            $csvFilePath = $validateData['tmp_name'];
            $csvFile = $validateData['name'];
            $csvFile = $this->getValidName($csvFile);
            $result = [
                'error' => false,
                'path' => $csvFilePath,
                'csv' => $csvFile,
                'extension' => $extension
            ];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Remove Special Characters From String
     *
     * @param string $string
     *
     * @return string
     */
    public function getValidName($string)
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        return preg_replace('/-+/', '-', $string);
    }

    /**
     * Read Csv File
     *
     * @param string $csvFilePath
     *
     * @return array
     */
    public function readCsv($csvFilePath)
    {
        try {
            $uploadedFileRowData = $this->_csvReader->getData($csvFilePath);
        } catch (\Exception $e) {
            $uploadedFileRowData = [];
        }
        return $uploadedFileRowData;
    }
}
