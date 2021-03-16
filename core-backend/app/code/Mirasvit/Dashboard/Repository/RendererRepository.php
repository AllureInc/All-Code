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



namespace Mirasvit\Dashboard\Repository;

use Mirasvit\Dashboard\Api\Data\RendererInterface;
use Mirasvit\Dashboard\Api\Repository\RendererRepositoryInterface;

class RendererRepository implements RendererRepositoryInterface
{
    /**
     * @var RendererInterface[]
     */
    private $pool = [];

    public function __construct(
        $pool = []
    ) {
        $this->pool = $pool;
    }

    public function getList()
    {
        return $this->pool;
    }

    public function get($identifier)
    {
        foreach ($this->getList() as $renderer) {
            if ($renderer->getIdentifier() == $identifier) {
                return $renderer;
            }
        }

        return false;
    }
}