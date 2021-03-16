<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\ProductToAmazon;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpAmazonConnector\Api\AmazonTempDataRepositoryInterface;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Product;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class UpdateStatus extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Webkul\MpAmazonConnector\Model\ProductMap
     */
    protected $productMap;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Webkul\MpAmazonConnector\Logger\Logger
     */
    private $logger;

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
        \Webkul\MpAmazonConnector\Helper\ProductOnAmazon $productOnAmazon,
        \Webkul\MpAmazonConnector\Logger\Logger $logger,
        MpProductFactory $mpProductFactory,
        TimezoneInterface $localeDate,
        ProductMapRepositoryInterface $productMapRepo,
        JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->mpProductFactory = $mpProductFactory;
        $this->productMap = $productMap;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->productOnAmazon = $productOnAmazon;
        $this->productMapRepo = $productMapRepo;
        $this->logger = $logger;
        $this->localeDate = $localeDate;
        $this->_productRepository = $productRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $sellerId = $this->helper->getCustomerId();
        try {
            $resultJson = $this->_resultJsonFactory->create();
            $productMapColl = $this->productMapRepo->getByAccountId($sellerId);
            $productMapColl->addFieldToFilter('export_status', 0);
            $feedIds = $this->getFeedIds($productMapColl);
            $response = $this->productOnAmazon->checkProductFeedStatus($feedIds);
            $recordsRemainging = $response['total_records'] - $response['updated_records'];
            if ($recordsRemainging) {
                if (!$response['updated_records']) {
                    $this->messageManager->addSuccess(__('Total %1 report(s) are not ready yet.', $recordsRemainging));
                } else {
                    $this->messageManager->addSuccess(__('Total %1 records updated successfully. %2 report(s) are not ready yet', $response['updated_records'], $response['total_records']));
                }
            } else {
                $this->messageManager->addSuccess(__('Total %1 records updated successfully.', $response['updated_records']));
            }
        } catch (\Exception $e) {
            $this->logger->info('CreateProduct Controller : '.$e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()
            ->setPath(
                'mpamazonconnect/product/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
    }

    /**
     * get all feed ids
     *
     * @param object $productMapColl
     * @return array
     */
    public function getFeedIds($productMapColl)
    {
        $feedArray = [];
        foreach ($productMapColl as $feed) {
            $product = $this->_productRepository->getById($feed->getMagentoProId());
            $feedArray[] = [
                'feed_id'=> $feed->getFeedsubmissionId(),
                'product_sku' =>$product->getSku()
            ];
        }
        return array_filter($feedArray);
    }
}
