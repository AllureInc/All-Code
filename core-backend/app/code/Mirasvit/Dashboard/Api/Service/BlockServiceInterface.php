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



namespace Mirasvit\Dashboard\Api\Service;

use Mirasvit\Dashboard\Api\Data\BlockInterface;

interface BlockServiceInterface
{
    /**
     * @param BlockInterface $block
     * @return array
     */
    public function getData(BlockInterface $block);

    //    /**
    //     * @param WidgetInterface $widget
    //     * @return string
    //     */
    //    public function toEmail(WidgetInterface $widget);
    //
    //    /**
    //     * @param WidgetInterface $widget
    //     * @return array
    //     */
    //    public function toArray(WidgetInterface $widget);
}