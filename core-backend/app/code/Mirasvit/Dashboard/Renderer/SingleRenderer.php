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



namespace Mirasvit\Dashboard\Renderer;

use Mirasvit\Dashboard\Api\Data\RendererInterface;

class SingleRenderer implements RendererInterface
{
    const ID    = 'single';
    const ALIAS = 'metrics';

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Single Value';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }
}