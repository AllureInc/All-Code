<?php
/**
 * @author     MangoIt
 * @package    MangoIt_EmailAttachments
 * @copyright  Copyright (c) 2015 MangoIt Solutions (http://www.mangoitsolutions.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MangoIt\EmailAttachments\Observer;

class BeforeSendCreditmemoCommentObserver extends AbstractSendCreditmemoObserver
{
    const XML_PATH_ATTACH_PDF = 'sales_email/creditmemo_comment/attachpdf';
    const XML_PATH_ATTACH_AGREEMENT = 'sales_email/creditmemo_comment/attachagreement';
}
