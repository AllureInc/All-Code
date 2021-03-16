<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_ShopConfigIE
 * @author    Mangoit
 */
namespace Mangoit\ShopConfigIE\Controller\Shopconfig;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;

class Export extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Webkul\MpMassUpload\Helper\Export
     */
    protected $_helperExport;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $_mpProduct;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var AdapterCsv
     */
    protected $_writer;

    /**
     * @var WriteFactory
     */
    protected $_filewrite;

    /**
     * @var ConvertArray
     */
    protected $_convertArray;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory,
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param AdapterCsv $writer
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
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        AdapterCsv $writer,
        WriteFactory $writeFactory,
        ConvertArray $convertArray,
        FileFactory $fileFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_helperExport = $helperExport;
        $this->_mpProduct = $mpProduct;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->_writer = $writer;
        $this->_filewrite = $writeFactory->create(BP);
        $this->_convertArray = $convertArray;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $mphelper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $isPartner = $mphelper->isSeller();

        if ($isPartner == 1) {
            if (count($this->getRequest()->getPost()) > 0) {

                try {
                    $helper = $this->_massUploadHelper;
                    $sellerId = $this->marketplaceHelper->getCustomerId();
                    $data = $this->getRequest()->getParams();
                    $fileContents = $fileName = $fileTyp = '';
                    $configDataRow = [];
                    $partner = $this->marketplaceHelper->getSeller();

                    /*
                     * Manipulate Data keys.
                     */
                    $partner['taxvat_number'] = $partner['taxvat'];
                    $partner['country_code'] = $partner['country_pic'];
                    $partner['background_color'] = $partner['background_width'];
                    $partner['company_url'] = $partner['website_url'];

                    /*
                     * Unset useless data from the seller data array.
                     */
                    unset($partner['entity_id']);
                    unset($partner['is_seller']);
                    unset($partner['seller_id']);
                    unset($partner['created_at']);
                    unset($partner['updated_at']);
                    unset($partner['moleskine_id']);
                    unset($partner['others_info']);
                    unset($partner['admin_notification']);
                    unset($partner['allowed_categories']);
                    unset($partner['is_profile_approved']);
                    unset($partner['taxvat']);
                    unset($partner['country_pic']);
                    unset($partner['moleskine_active']);
                    unset($partner['background_width']);
                    unset($partner['shop_url']);
                    unset($partner['website_url']);
                    unset($partner['profile_admin_notification']);
                    unset($partner['trustworthy']);
                    unset($partner['become_seller_request']);
                    unset($partner['payment_source']);
                    unset($partner['shop_font']);
                    unset($partner['account_deactivate']);
                    unset($partner['product_watermark_image']);
                    unset($partner['watermark_opacity']);
                    unset($partner['watermark_image_size']);
                    unset($partner['watermark_image_position']);
                            



                    $configDataRow[0] = array_keys($partner); // Added headers in CSV array.
                    $configDataRow[1][0] = $partner; // Added main data to CSV array.

                    if (count($partner)) {
                        if($data['export_type'] == "CSV") {
                            $writer = $this->_writer;
                            $writer->setHeaderCols($configDataRow[0]);
                            foreach ($configDataRow[1] as $dataRow) {
                                if (!empty($dataRow)) {
                                    $writer->writeRow($dataRow);
                                }
                            }
                            $fileTyp = 'text/csv';
                            $fileName = 'shop_config_data.csv';
                            $fileContents = $writer->getContents();

                        } else if ($data['export_type'] == "JSON") {
                            $filePathDirNm = 'shop_config_data.json';
                            $jsonArray = array_map(function($v){
                                    return (is_null($v)) ? "" : $v;
                                }, $partner);
                            $this->_filewrite->writeFile(
                                    $filePathDirNm,
                                    json_encode($jsonArray, JSON_PRETTY_PRINT)
                                );
                            $fileTyp = 'text/json';
                            $fileName = 'shop_config_data.json';
                            $fileContents = $this->_filewrite->readFile($filePathDirNm);

                        } else if ($data['export_type'] == "Excel") {

                            $data = array_map(function($v){
                                    return (is_null($v)) ? " " : $v;
                                }, $configDataRow[1]);
                            $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));

                            $convert->setDataHeader($configDataRow[0]);

                            $fileTyp = ''; //'text/json';
                            $fileName = 'shop_config_data.xls';
                            $fileContents = $convert->convert('single_sheet');

                            $fileContents = str_replace(
                                    '</Workbook>',
                                    '<Styles></Styles><Styles></Styles></Workbook>',
                                    $fileContents
                                );

                        } else if ($data['export_type'] == "XML") {
                            $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><ShopConfig></ShopConfig>");

                            foreach ($configDataRow[1][0] as $key => $value) {
                                if (is_string($key)) {
                                    // $hasStringKey = true;
                                    $xml->addChild($key, $value);

                                } elseif (is_int($key)) {
                                    // $hasNumericKey = true;
                                    $xml->addChild($key, $value);
                                }
                            }

                            $fileTyp = '';
                            $fileName = 'shop_config_data.xml';
                            $fileContents = $xml->asXML();
                        }

                        if ($fileContents != '' && $fileName != '') {
                            /**/
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
                            /**/
                        }

                    } else {
                        $this->messageManager->addError(
                            __("There is no product with product type: %1 to export.", $productType)
                        );
                    }
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/export',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/export',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $resultPage = $this->_resultPageFactory->create();
                if ($this->marketplaceHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mitsshopconfig_layout2_shopconfig_export');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Shop Configuration Export')
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
}
