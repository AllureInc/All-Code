<?php
namespace Mangoit\Fskverified\Controller\Fsk;

use Magento\Framework\App\Action\Action;

class Test extends Action
{
	protected $_objectManager;

	public function __construct(\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager )
	{
		$this->_objectManager = $objectmanager;
		parent::__construct($context);
	}
    
    public function execute()
    {  

        $mail = new PHPMailer();

// Settings
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';

$mail->Host       = "mail.mygermany.com "; // SMTP server example
$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Port       = 587;                    // set the SMTP port for the GMAIL server
$mail->Username   = "info.store@mygermany.com"; // SMTP account username example
$mail->Password   = "4fMzvte%}J=ZUe";        // SMTP account password example

// Content
$mail->isHTML(true);                                  // Set email format to HTML
$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

$mail->send();
       /* 
        $to = 'freight@mygermany.com';
        $subject = 'the subject';
        $message = 'hello, This is a test mail.';
        $headers = 'From: info.store@mygermany.com' . "\r\n" .
        'Reply-To: info.store@mygermany.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

        echo mail($to, $subject, $message, $headers);*/
        die("Email has been sent");
        /*$attrType = ['select', 'multiselect', 'price'];
        $productAttr = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory');
        $attributes = $productAttr->create();
        echo "<pre>";
        foreach ($attributes as $attr) {
            if (!in_array($attr->getFrontendInput(), $attrType)) {
                echo "<br> name: ".$attr->getName();
                $attr->setIsFilterable(0);
                $attr->setIsSearchable(0);
                $attr->setIsFilterableInSearch(0);
                $attr->setUsedForSortBy(0);
                $attr->setIsVisibleInAdvancedSearch(0);
                $attr->save();
            }        
        }*/

    }
}
