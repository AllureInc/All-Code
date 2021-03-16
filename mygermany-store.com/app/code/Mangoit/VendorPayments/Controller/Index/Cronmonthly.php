<?php
namespace Mangoit\VendorPayments\Controller\Index;
/* last edit 29-Jan-2019 */

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Cronmonthly extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Mangoit\Marketplace\Cron\WeeklyInvoiceGeneration
     */
    protected $_monthlyCron;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Mangoit\Marketplace\Cron\MonthlyInvoiceGeneration $monthlyCron,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_monthlyCron = $monthlyCron;
        parent::__construct($context);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_monthlyCron->execute();
        echo "<br> Monthly Invocie Generation Cron is running....";
    }
}
