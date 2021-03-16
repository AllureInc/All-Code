<?php

namespace Kerastase\Aramex\Controller\Adminhtml\Import;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    public $_storeManager;
    /**
     * @var \Valomnia\ImportOrder\Model\Parser
     */
    protected $parser;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    protected $cart;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Kerastase\Aramex\Model\ImportExcel $parser,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Kerastase\Aramex\Logger\Logger $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory

    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->parser = $parser;
        $this->formKey = $formKey;
        $this->directory = $directoryList;
        $this->logger = $logger;
        $this->salesCollection = $salesOrderCollectionFactory;

    }



    public function execute()
    {

        $this->logger->info('########### Import Order FROM ADMIN ##############');
        $message = "";
        $error = false;

        try{
            $rootPath = $this->directory->getRoot();
            $target = $rootPath.'/pub/media/tmp';
            $orderResult=[];
            //attachment is the input file name posted from your form
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'attachment']);

            $_fileType = $uploader->getFileExtension();
            $newFileName = uniqid().'.'.$_fileType;

            /** Allowed extension types */
            $uploader->setAllowedExtensions([ 'xls', 'xlsx']);
            /** rename file name if already exists */
            $uploader->setAllowRenameFiles(true);

            $result = $uploader->save($target, $newFileName); //Use this if you want to change your file name
            $FilePath = $target . '/' . $newFileName;
            $data = $this->parser->readFile($FilePath);

            foreach ($data as $row => $data) {
                $awb = $data[0];
                 /********** get order from awb sent from Aramex*/
                $orders = $this->salesCollection->create();
                $order = $orders->addFieldToFilter('shipment_awb', $awb)->getFirstItem();
                if($order->getData()!=null){

                    $this->logger->info(' ## ORDER MATCHING WITH AWB '.$awb.' IS ',array($order->getData()));
                    $orderResult [] = ['id'=>$order->getData('entity_id'),'order_increment_id'=>$order->getData('increment_id'),'awb'=>$awb,'amount_aramex'=>intval($data[1]),'amount_to_pay'=>intval($order->getData('grand_total')),'order_status'=>$order->getData('status')];
                }else{
                    $this->logger->info(' ## NO ORDER MATCHING WITH AWB '.$awb);
                }
            }

        } catch (\Exception $e) {
            $error = true;
            $message = $e->getMessage();
        }

        return  $this->resultJsonFactory->create()->setData(['result'=>$orderResult,'error'=>$error,'message'=>$message]);

    }


    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }

}
