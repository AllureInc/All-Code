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

use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;

interface BlockRepositoryInterface
{
    /**
     * @param BoardInterface $board
     * @return BlockInterface[]
     */
    public function getList(BoardInterface $board);

    /**
     * @return BlockInterface
     */
    public function create();

    /**
     * @param BoardInterface $board
     * @param int $blockId
     * @return BlockInterface|false
     */
    public function get(BoardInterface $board, $blockId);

    /**
     * @param BoardInterface $board
     * @param BlockInterface $block
     * @return BlockInterface
     */
    public function save(BoardInterface $board, BlockInterface $block);

    /**
     * @param array $data
     * @return BlockInterface
     */
    public function initFromArray(array $data);
}