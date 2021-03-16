<?php
/**
 * @author     MangoIt
 * @package    MangoIt_EmailAttachments
 * @copyright  Copyright (c) 2015 MangoIt Solutions (http://www.mangoitsolutions.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MangoIt\EmailAttachments\Model\Api;

interface AttachmentInterface
{
    public function getMimeType();

    public function getFilename();

    public function getDisposition();

    public function getEncoding();

    public function getContent();
}
