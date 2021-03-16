<?php
/**
 * @author     MangoIt
 * @package    MangoIt_EmailAttachments
 * @copyright  Copyright (c) 2015 MangoIt Solutions (http://www.mangoitsolutions.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MangoIt\EmailAttachments\Model;

class MailTransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * Clears the sender from the mail
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function clearFrom()
    {
        //$this->_from = null;
        $this->message->clearFrom('From');
        return $this;
    }
 
    public function clearSubject()
    {
        $this->message->clearSubject();
        return $this;
    }
 
    public function clearMessageId()
    {
        $this->message->clearMessageId();
        return $this;
    }
 
    public function clearBody()
    {
        $this->message->setParts([]);
        return $this;
    }
 
    public function clearRecipients()
    {
        $this->message->clearRecipients();
        return $this;
    }
 
    /**
     * Clear header from the message
     *
     * @param string $headerName
     * @return Zend_Mail Provides fluent inter
     */
    public function clearHeader($headerName)
    {
        if (isset($this->_headers[$headerName])){
            unset($this->_headers[$headerName]);
        }
        return $this;
    }
    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment1(Api\AttachmentInterface $attachment)
    {
        $this->message->createAttachment(
            $attachment->getContent(),
            $attachment->getMimeType(),
            $attachment->getDisposition(),
            $attachment->getEncoding(),
            $this->encodedFileName($attachment->getFilename())
        );
    }

    protected function encodedFileName($subject)
    {
        return sprintf('=?utf-8?B?%s?=', base64_encode($subject));
    }
}
