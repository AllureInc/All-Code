<?php

namespace Mangoit\VendorPayments\Controller\Adminhtml\CustomerInvoices;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action
{
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var Mangoit\VendorPayments\Helper\CustomerInvoice
     */
    protected $invoiceHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Mangoit\VendorPayments\Helper\CustomerInvoice $invoiceHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->invoiceHelper = $invoiceHelper;
        $this->fileFactory = $fileFactory;
    }
    public function execute()
    {
    	$post = $this->getRequest()->getPost();
    	// print_r($post);
    	// die('asdasd');
    	// print_r($post['from']);

    	if($post['download_type'] == 'customer') {
	    	$csvData = $this->invoiceHelper->getCustomerCSVContent($post['from'], $post['to']);

	    	$this->fileFactory->create(
		        'AusgangsrechnungCSV.csv',
		        $csvData,
		        DirectoryList::VAR_DIR,
		        'text/csv'
		    );
    	} elseif($post['download_type'] == 'vendor') {
    		$csvData = $this->invoiceHelper->getVendorCSVContent($post['from'], $post['to']);

	    	$this->fileFactory->create(
		        'Eingangsrechnung-GutschriftCSV.csv',
		        $csvData,
		        DirectoryList::VAR_DIR,
		        'text/csv'
		    );
    	}
        die('asdfadsfadsf');
	}
}
