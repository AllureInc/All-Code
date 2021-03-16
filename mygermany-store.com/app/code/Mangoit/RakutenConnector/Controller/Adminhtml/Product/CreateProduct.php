<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface;
use Mangoit\RakutenConnector\Controller\Adminhtml\Product;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CreateProduct extends Product
{
    /**
     * @var \Mangoit\RakutenConnector\Model\ProductMap
     */
    protected $_productMap;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Mangoit\RakutenConnector\Logger\Logger
     */
    private $logger;

    /**
     * @param Context                                       $context
     * @param \Mangoit\RakutenConnector\Model\ProductMap $productMap
     * @param \Magento\Framework\Json\Helper\Data           $jsonHelper
     * @param \Mangoit\RakutenConnector\Helper\Data      $helper
     * @param \Mangoit\RakutenConnector\Helper\Product   $productHelper
     */
    public function __construct(
        Context $context,
        \Mangoit\RakutenConnector\Model\ProductMap $productMap,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Mangoit\RakutenConnector\Helper\Product $productHelper,
        \Mangoit\RakutenConnector\Helper\ManageProductRawData $manageProductRawData,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        MpProductFactory $mpProductFactory,
        TimezoneInterface $localeDate,
        \Magento\Framework\ObjectManagerInterface $objectManager
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
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $sellerId = $this->helper->getCustomerId($id);
        try {
            $tempData = $this->helper
                        ->getTotalImported('product', $sellerId);

            $request=$this->getRequest();
            $this->helper->getRktnClient(true);
            if ($tempData && $tempData->getEntityId()) {
                $backendSession = $this->objectManager->get(
                    '\Magento\Backend\Model\Session'
                );
                $backendSession->setAmzSession('start');
                $this->helper->getRktnClient(true);
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
                            'rakuten_pro_id'     => $tempData->getItemId(),
                            'name'              => $this->productName,
                            'product_type'      => $this->productType,
                            'seller_id'         => $sellerId,
                            'rkt_product_id'    => $tempData->getRktProductId(),
                            'product_sku'       => $this->sku
                        ];

                        if (isset($result['product_id']) && $result['product_id']) {
                            $data['magento_pro_id'] = $result['product_id'];
                            $data['mage_cat_id'] = $tempProData['category'][0];
                            $record = $this->productMap;
                            $record->setData($data)->save();
                            if ($sellerId) {
                                $this->productHelper->
                                    _saveMarketPlaceInfo($result['product_id'], $sellerId);
                            }
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
                $backendSession->setAmzSession('');
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
                        ->productHasRktnVariations($asin, $tempProData);

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
