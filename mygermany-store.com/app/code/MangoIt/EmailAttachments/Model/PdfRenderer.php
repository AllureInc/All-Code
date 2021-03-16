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

class PdfRenderer implements Api\PdfRendererInterface
{
    protected $pdfRenderer;

    public function __construct(
        \Magento\Sales\Model\Order\Pdf\AbstractPdf $pdfRenderer
    ) {
        $this->pdfRenderer = $pdfRenderer;
    }

    public function getPdfAsString(array $salesObject)
    {
        return $this->pdfRenderer->getPdf($salesObject)->render();
    }

    public function getFileName($input = '')
    {
        return sprintf('%s.pdf', $input);
    }

    public function canRender()
    {
        return true;
    }
}
