<?php

/**
 * Sparsh_SalesEmailAttachments
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SalesEmailAttachments\Mail;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Sparsh\SalesEmailAttachments\Helper\Data;
use Zend_Mime_Part;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Class SenderBuilder
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Invoice
     */
    private $invoice;
    /**
     * @var Creditmemo
     */
    private $creditmemo;
    /**
     * @var Shipment
     */
    private $shipment;
    /**
     * @var InvoiceCollectionFactory
     */
    private $invoiceCollectionFactory;
    /**
     * @var CreditmemoCollectionFactory
     */
    private $creditmemoCollectionFactory;
    /**
     * @var ShipmentCollectionFactory
     */
    private $shipmentCollectionFactory;
    /**
     * @var Data
     */
    private $data;
    /**
     * @var File
     */
    private $file;

    /**
     * SenderBuilder constructor.
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param Filesystem $filesystem
     * @param Invoice $invoice
     * @param Creditmemo $creditmemo
     * @param Shipment $shipment
     * @param Data $data
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param File $file
     * @param TransportBuilderByStore|null $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        Filesystem $filesystem,
        Invoice $invoice,
        Creditmemo $creditmemo,
        Shipment $shipment,
        Data $data,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        File $file,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        $this->filesystem = $filesystem;
        $this->transportBuilder = $transportBuilder;
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->shipment = $shipment;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->data = $data;
        $this->file = $file;
        parent::__construct($templateContainer, $identityContainer, $transportBuilder, $transportBuilderByStore);
    }

    /**
     * @throws \Zend_Mime_Exception
     * @throws \Zend_Pdf_Exception
     */
    protected function configureEmailTemplate()
    {
        $mimeTypes = [
            'txt' => 'text/plain',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/msword'
        ];

        $templateVars = $this->templateContainer->getTemplateVars();
        if ($this->data->getGeneralConfig('attach_pdf_enabled')) {
            $pdf = [];
            if (isset($templateVars['invoice']) && in_array('invoice', explode(",", $this->data->getGeneralConfig('attach_pdf_for')))) {
                $invoices = $this->invoiceCollectionFactory->create()
                    ->addFieldToFilter('entity_id', $templateVars['invoice']['entity_id']);
                $pdf = $this->invoice->getPdf($invoices);
                $fileContent = new Zend_Mime_Part($pdf->render());
                $fileName = 'invoice' . $templateVars['invoice']['increment_id'] . '.pdf';

            } elseif (isset($templateVars['creditmemo']) && in_array('creditmemo', explode(",", $this->data->getGeneralConfig('attach_pdf_for')))) {

                $creditmemos = $this->creditmemoCollectionFactory->create()
                    ->addFieldToFilter('entity_id', $templateVars['creditmemo']['entity_id']);
                $pdf = $this->creditmemo->getPdf($creditmemos);
                $fileContent = new Zend_Mime_Part($pdf->render());
                $fileName = 'creditmemo' . $templateVars['creditmemo']['increment_id'] . '.pdf';

            } elseif (isset($templateVars['shipment']) && in_array('shipment', explode(",", $this->data->getGeneralConfig('attach_pdf_for')))) {

                $shipments = $this->shipmentCollectionFactory->create()
                    ->addFieldToFilter('entity_id', $templateVars['shipment']['entity_id']);
                $pdf = $this->shipment->getPdf($shipments);
                $fileContent = new Zend_Mime_Part($pdf->render());
                $fileName = 'shipment' . $templateVars['shipment']['increment_id'] . '.pdf';

            }
            if (!empty($pdf)) {
                $this->transportBuilder->addAttachment($fileContent->getContent(), $fileName, 'application/pdf');
            }
        }
        if ($this->data->getGeneralConfig('attach_terms_and_conditions_enabled')) {
            $attachFor = explode(",", $this->data->getGeneralConfig('attach_terms_and_conditions_for'));
            $emailType = $this->getEmailType($templateVars);
            if (in_array($emailType, $attachFor) && !empty($this->data->getGeneralConfig('terms_conditions'))) {
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('sparsh/sales_email_attachments/');
                $filepath = $destinationPath . $this->data->getGeneralConfig('terms_conditions');
                $content = $this->file->fileGetContents($filepath);
                $fileName = explode("/", $this->data->getGeneralConfig('terms_conditions'));
                $type = explode(".", $this->data->getGeneralConfig('terms_conditions'));
                $mimeType = $mimeTypes[$type[count($type) - 1]];
                $this->transportBuilder->addAttachment($content, $fileName[count($fileName) - 1], $mimeType);
            }

        }
        if (!empty($this->data->getGeneralConfig('cc'))) {
            $cc = explode(",", $this->data->getGeneralConfig('cc'));
            foreach ($cc as $item) {
                $this->transportBuilder->addCc($item);
            }
        }
        if (!empty($this->data->getGeneralConfig('bcc'))) {
            $bcc = explode(",", $this->data->getGeneralConfig('bcc'));
            foreach ($bcc as $item) {
                $this->transportBuilder->addBcc($item);
            }
        }
        parent::configureEmailTemplate();
    }

    /**
     * @param $templateVars
     * @return mixed
     */
    public function getEmailType($templateVars)
    {
        $emailTypes = ['invoice', 'shipment', 'creditmemo', 'order'];
        foreach ($emailTypes as $emailType) {
            if (isset($templateVars[$emailType])) {
                return $emailType;
            }
        }
    }
}
