<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpAmazonConnector\Api\AmazonTempDataRepositoryInterface;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Product;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Session\SessionManager;

class CreateProduct extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Webkul\MpAmazonConnector\Model\ProductMap
     */
    private $productMap;
    
        /**
         * @var \Magento\Framework\Json\Helper\Data
         */
    private $jsonHelper;
    
        /**
         * @var \Webkul\MpAmazonConnector\Helper\Data
         */
    private $helper;
    
        /**
         * @var \Webkul\MpAmazonConnector\Helper\Product
         */
    private $productHelper;
    
        /**
         * @var \Webkul\MpAmazonConnector\Logger\Logger
         */
    private $logger;
    
    private $productName;
    
    private $sku;
    
    private $productType;

    /**
     * @param Context                                       $context
     * @param \Webkul\MpAmazonConnector\Model\ProductMap $productMap
     * @param \Magento\Framework\Json\Helper\Data           $jsonHelper
     * @param \Webkul\MpAmazonConnector\Helper\Data      $helper
     * @param \Webkul\MpAmazonConnector\Helper\Product   $productHelper
     */
    public function __construct(
        Context $context,
        \Webkul\MpAmazonConnector\Model\ProductMap $productMap,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Webkul\MpAmazonConnector\Helper\Product $productHelper,
        \Webkul\MpAmazonConnector\Helper\ManageProductRawData $manageProductRawData,
        \Webkul\MpAmazonConnector\Logger\Logger $logger,
        MpProductFactory $mpProductFactory,
        TimezoneInterface $localeDate,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession
    ) {
        parent::__construct($context);
        $this->mpProductFactory = $mpProductFactory;
        $this->productMap = $productMap;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->manageProductRawData = $manageProductRawData;
        $this->logger = $logger;
        $this->localeDate = $localeDate;
        $this->objectManager = $objectManager;
        $this->coreSession = $coreSession;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $sellerId = $this->helper->getCustomerId();
        try {
            $amzClient = $this->helper->getAmzClient();
            $tempData = $this->helper
                        ->getTotalImported('product', $sellerId);
            $request=$this->getRequest();
            $this->coreSession->setData('amz_session', 'start');
            if(count($tempData)) {
                if ($tempData->getEntityId()) {
                    $backendSession = $this->objectManager->get(
                        '\Magento\Backend\Model\Session'
                    );
                    $backendSession->setAmzSession('start');
                    $this->helper->getAmzClient();
                    $tempData = $this->helper
                                ->getTotalImported('product', $sellerId);
                    $request=$this->getRequest();
                    $tempProData = json_decode($tempData->getItemData(), true);
                    $result = [];
                    $importType = $this->helper->getImportTypeOfProduct();
                    $this->productName = $tempProData['name'];
                    $this->sku = $tempProData['sku'];

                    if (!empty($tempProData)) {
                        $this->productType = $tempProData['type_id'];
                        if ($this->productHelper->isProductMapped($tempData->getItemId(), $this->sku)) {
                            $result = $this->processedTempData(
                                $importType,
                                $tempProData,
                                $tempData->getItemId(),
                                $request
                            );
                            $data = [
                                'amazon_pro_id'     => $tempData->getItemId(),
                                'name'              => $this->productName,
                                'product_type'      => $this->productType,
                                'seller_id'         => $sellerId,
                                'amz_product_id'    => $tempData->getAmzProductId(),
                                'product_sku'       => $this->sku
                            ];

                            if (isset($result['product_id']) && $result['product_id']) {
                                $data['magento_pro_id'] = $result['product_id'];
                                $data['mage_cat_id'] = $tempProData['category'][0];
                                $record = $this->productMap;
                                $record->setData($data)->save();
                                $this->productHelper->_saveMarketPlaceInfo($result['product_id'], $sellerId);
                            }
                        } else {
                            $result['error'] = 1;
                            $result['msg'] = 'Skipped '.$tempProData['name'].". sku '".
                                            $tempProData['sku']."' already mapped.";
                        }
                    } else {
                        $result = [
                            'error' => true,
                            'msg' => 'Data not found'
                        ];
                    }
                    $tempData->delete();
                    $this->coreSession->setData('amz_session', '');
                } else {
                    $data = $this->getRequest()->getParams();
                    $total = (int) $data['count'] - (int) $data['skip'];
                    $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Product(s) Imported.').'</div>';
                    $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
                    $result['msg'] = $msg;
                }
            } else {
                $data = $this->getRequest()->getParams();
                $total = (int) $data['count'] - (int) $data['skip'];
                $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Product(s) Imported.').'</div>';
                $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
                $result['msg'] = $msg;
            }
        } catch (\Exception $e) {
            $this->logger->info('CreateProduct Controller : '.$e->getMessage());
            $result = [
                    'error' => true,
                    'msg' => $e->getMessage()
                ];
        }
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    /**
     * processed amazon temp data
     *
     * @param string $importType
     * @param array $tempProData
     * @param string $asin
     * @param object $request
     * @return array
     */
    private function processedTempData($importType, $tempProData, $asin, $request)
    {
        if (empty($importType)) {
                $result = $this->_getSimpleProductResponse($tempProData, $request);
        } else {
            $hasVariation = $this->manageProductRawData
                        ->amzProHasVariation($asin, $tempProData);

            if (!empty($hasVariation['is_simple'])) {
                $result = $this->_getSimpleProductResponse($hasVariation['data'], $request);
            } else {
                foreach ($hasVariation['data'] as $key => $value) {
                    $request->setParam($key, $value);
                }
                $result = $this->productHelper
                        ->saveConfigProduct($request);
                $this->productType = 'Configurable';
                $this->productName = $hasVariation['data']['name'];
                $this->sku = $hasVariation['data']['sku'];
            }
        }
        return $result;
    }

    /**
     * get simple product data response
     *
     * @param array $tempProData
     * @param object $request
     * @return array
     */
    private function _getSimpleProductResponse($tempProData, $request)
    {
        $result = null;
        try {
            $isSkuExist = $this->productHelper->isValidSku($tempProData['sku']);
            if ($isSkuExist) {
                foreach ($tempProData as $key => $value) {
                    $request->setParam($key, $value);
                }
                $result = $this->productHelper
                        ->saveSimpleProduct($request);
            } else {
                $result = $this->productHelper
                        ->getExistingSkuData($tempProData['sku']);
            }
        } catch (\Exception $e) {
            $this->logger->info('createProduct _getSimpleProductResponse : '.$e->getMessage());
        }
        return $result;
    }
}
