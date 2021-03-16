<?php
namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\OrderInvoiceInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\UrlInterface;

class OrderInvoice implements OrderInvoiceInterface
{

    /**
     * @var orderInterface
     */
    protected $orderInterface;

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var customerRepositoryInterface
    */    
    protected $_customerRepositoryInterface;

    /**
     * @var Pdf\AbstractPdf
     */
    protected $pdfRenderer;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var Magento\Framework\Filesystem\Io\File
     */
    protected $_file;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfRenderer,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        StoreManagerInterface $storeManager
    ) {
        $this->orderInterface = $orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->pdfRenderer = $pdfRenderer;
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        $this->storeManager = $storeManager;
    }
    /**
     * Order Invoice
     *
     * @api
     * @param string $orderid is a Magento Order id
     * @return string success or failure message
     */
    public function orderinvoice($orderid) {
        // $objectManager = ObjectManager::getInstance();
        $resultJson = $this->resultJsonFactory->create();
        $orderDetails = $this->orderInterface->load($orderid);
        if ($orderDetails->getId()) {
            // $actualOrderStatus = $orderDetails->getStatus();
            $invoices = [];
            if ($orderDetails->getInvoiceCollection()) {
                foreach ($orderDetails->getInvoiceCollection() as $invoice) {
                    $invoice_id = $invoice->getIncrementId();
                    $pdf = $this->pdfRenderer->getPdf([$invoice]);
                    // print_r($pdf->render());
                    // $pubPath = 'pub/media';
                    $filePath = "/mangoit/order_invoices/".$orderid.'/';
                    $pdfPath = $this->_directoryList->getPath('media').$filePath;
                    $ioAdapter = $this->_file;
                    if (!is_dir($pdfPath)) {
                        $ioAdapter->mkdir($pdfPath, 0775);
                    }
                    $fileName = 'RE_'.$invoice_id.".pdf";
                    $ioAdapter->open(array('path'=>$pdfPath));
                    $ioAdapter->write($fileName, $pdf->render(), 0775);

                    $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);


                    $invoices[$invoice_id] = [
                            /*'content' => base64_encode($pdf->render()),*/
                            'src' => $mediaUrl.$filePath.$fileName
                        ];
                }
                // print_r($invoices);
                // die('asdasdsa');
                return $invoices;
            } else {
                throw new InputException(__('Order has no invoices.'));
            }
        } else {
            throw new NoSuchEntityException(__('Order not found!'));
        }
    }
}