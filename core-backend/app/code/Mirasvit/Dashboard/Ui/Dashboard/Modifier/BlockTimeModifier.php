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



namespace Mirasvit\Dashboard\Ui\Dashboard\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;

class BlockTimeModifier implements ModifierInterface
{
    /**
     * @var DateServiceInterface
     */
    private $dateService;

    public function __construct(DateServiceInterface $dateService)
    {
        $this->dateService = $dateService;
    }

    /**
     * Add interval label for chosen time range code.
     *
     * @param mixed[] $data
     *
     * @return \mixed[]
     */
    public function modifyData(array $data)
    {
        $blocksKey = \Mirasvit\Dashboard\Api\Data\BoardInterface::BLOCKS;
        foreach ($data['boards'] as $boardId => $board) {
            foreach ($board[$blocksKey] as $blockId => $block)
            if (isset($block['time']['range'])) {
                $label = $this->dateService->getIntervalHint($block['time']['range']);
                $data['boards'][$boardId][$blocksKey][$blockId]['time']['range_label'] = $label;
            }
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}