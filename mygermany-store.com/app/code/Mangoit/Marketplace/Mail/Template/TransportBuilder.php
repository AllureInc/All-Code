<?php
namespace Mangoit\Marketplace\Mail\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    // public function addAttachment($pdfString, $filename)
    // {
    //     If ($filename == '') {
    //       $filename="attachment";
    //     }
    //     $this->message->createAttachment(
    //       $pdfString,
    //       'application/pdf',
    //       \Zend_Mime::DISPOSITION_ATTACHMENT,
    //       \Zend_Mime::ENCODING_BASE64,
    //       $filename.'.pdf'
    //     );
    //    return $this;
    // }

    public function addAttachment(
        $body,
        $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding    = Zend_Mime::ENCODING_BASE64,
        $filename    = null
    ) {
        $this->message->createAttachment($body, $mimeType, $disposition, 
            $encoding, $filename);
        return $this;
    }
}