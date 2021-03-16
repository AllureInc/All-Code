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
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Api\Repository;

use Mirasvit\Dashboard\Api\Data\DataSourceInterface;

interface DataSourceRepositoryInterface
{
    /**
     * @return DataSourceInterface[]
     */
    public function getList();

    /**
     * @param string $identifier
     * @return DataSourceInterface
     */
    public function get($identifier);
}