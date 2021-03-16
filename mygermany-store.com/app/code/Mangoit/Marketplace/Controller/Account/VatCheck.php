<?php
namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class VatCheck extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderObj;
    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $salesList;
    protected $sellerProduct;
    protected $productRepository;
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        \Webkul\Marketplace\Model\Product $sellerProduct,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->salesList = $salesList;
        $this->orderObj = $orderObj;
        $this->sellerProduct = $sellerProduct;
        $this->productRepository = $productRepository;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $response = [];
       /* $vatNumber = preg_replace("'/^[a-z]{2}/i", "", $this->getRequest()->getParam('vat')); 
        $varValid = $this->_objectManager->get(\Magento\Customer\Model\Vat::class)
            ->checkVatNumber(
                $this->getRequest()->getParam('country'),
                $vatNumber
            );*/



        /*$response['status'] = $varValid->getIsValid();
        $response['msg'] = $varValid->getRequestMessage();*/

        $vat_result = $this->misCheckVatNumber($this->getRequest()->getParam('vat'));

        if ($vat_result == true) {
            $response['status'] = true;
            $response['msg'] = __('');
        } else {
            $response['status'] = false;
            $response['msg'] = __('Please enter a valid VAT number.');
        }
        

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
        return;   
    }

    public function misCheckVatNumber($vat)
    {
        $result = false;
        $valid_expression_array = [
            '/^(AT)U(\d{8})$/',
            '/^(BE)(0?\d{9})$/',
            '/^(BE)([0-1]\d{9})$/',
            '/^(BG)(\d{9,10})$/',
            '/^(CHE)(\d{9})(MWST|TVA|IVA)?$/',
            '/^(CY)([0-59]\d{7}[A-Z])$/',
            '/^(CZ)(\d{8,10})(\d{3})?$/',
            '/^(DE)([1-9]\d{8})$/',
            '/^(DK)(\d{8})$/',
            '/^(EE)(10\d{7})$/',
            '/^(EL)(\d{9})$/',
            '/^(ES)([A-Z]\d{8})$/',
            '/^(ES)([A-HN-SW]\d{7}[A-J])$/',
            '/^(ES)([0-9YZ]\d{7}[A-Z])$/',
            '/^(ES)([KLMX]\d{7}[A-Z])$/',
            '/^(EU)(\d{9})$/',
            '/^(FI)(\d{8})$/',
            '/^(FR)(\d{11})$/',
            '/^(FR)([A-HJ-NP-Z]\d{10})$/',
            '/^(FR)(\d[A-HJ-NP-Z]\d{9})$/',
            '/^(FR)([A-HJ-NP-Z]{2}\d{9})$/',
            '/^(GB)?(\d{9})$/',
            '/^(GB)?(\d{12})$/',
            '/^(GB)?(GD\d{3})$/',
            '/^(GB)?(HA\d{3})$/',
            '/^(HR)(\d{11})$/',
            '/^(HU)(\d{8})$/',
            '/^(IE)(\d{7}[A-W])$/',
            '/^(IE)([7-9][A-Z\*\+)]\d{5}[A-W])$/',
            '/^(IE)(\d{7}[A-W][AH])$/',
            '/^(IT)(\d{11})$/',
            '/^(LV)(\d{11})$/',
            '/^(LT)(\d{9}|\d{12})$/',
            '/^(LU)(\d{8})$/',
            '/^(MT)([1-9]\d{7})$/',
            '/^(NL)(\d{9}B\d{2})$/',
            '/^(NL)([A-Z0-9\*\+]{10}\d{2})$/',
            '/^(NO)(\d{9})$/',
            '/^(PL)(\d{10})$/',
            '/^(PT)(\d{9})$/',
            '/^(RO)([1-9]\d{1,9})$/',
            '/^(RU)(\d{10}|\d{12})$/',
            '/^(RS)(\d{9})$/',
            '/^(SI)([1-9]\d{7})$/',
            '/^(SK)([1-9]\d[2346-9]\d{7})$/',
            '/^(SE)(\d{10}01)$/'
        ];

        foreach ($valid_expression_array as $expression) {
            if (preg_match($expression,$vat)) {
                $result = true;
                return $result;
                exit();
            }
        }

        return $result = false;
        exit();
    }
}