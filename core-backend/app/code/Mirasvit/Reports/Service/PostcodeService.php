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



namespace Mirasvit\Reports\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Backend\App\Action\Context;

class PostcodeService
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PostcodeService constructor.
     *
     * @param ResourceConnection $resource
     * @param Context $context
     */
    public function __construct(
        ResourceConnection $resource,
        Context $context
    ) {
        $this->resource = $resource;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($excluded)
    {
        try {
            $tableName = $this->resource->getTableName('mst_reports_postcode');
            $connection = $this->resource->getConnection();

            if ('false' === $excluded) {
                $connection->query("DELETE from " . $tableName);
            }

            if (is_array($excluded)) {
                $excluded = "(" . implode(',', $excluded) . ")";
                $connection->query("DELETE from " . $tableName . " WHERE postcode_id NOT IN " . $excluded);
            }

            $this->messageManager->addSuccessMessage(
                __('Record(s) were removed from the postcode table.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this;
    }

}