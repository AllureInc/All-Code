<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\ProductToRakuten;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface;
use Mangoit\RakutenConnector\Controller\Adminhtml\Product;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class UpdateStatus extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Mangoit\RakutenConnector\Model\ProductMap
     */
    protected $productMap;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Product
     */
    protected $productHelper;

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
        \Mangoit\RakutenConnector\Helper\ProductOnRakuten $productOnRakuten,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
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
        $this->productOnRakuten = $productOnRakuten;
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
            $response = $this->productOnRakuten->checkProductFeedStatus($feedIds);
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
                'rakutenconnect/product/index',
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
