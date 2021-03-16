<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;

class Debug extends Template
{
    private $registry;

    public $provider;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        SchemaInterface $provider,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->provider = $provider;

        parent::__construct($context, $data);
    }

    /**
     * @return ReportInterface
     */
    public function getReport()
    {
        return $this->registry->registry('current_report');
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_request->getParam('debug', false);
    }
}