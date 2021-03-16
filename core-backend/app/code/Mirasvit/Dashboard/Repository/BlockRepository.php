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

use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BlockRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Api\Data\BlockInterfaceFactory;

class BlockRepository implements BlockRepositoryInterface
{
    /**
     * @var BlockInterfaceFactory
     */
    protected $blockFactory;

    /**
     * @var BoardRepositoryInterface
     */
    protected $boardRepository;

    public function __construct(
        BlockInterfaceFactory $widgetFactory,
        BoardRepositoryInterface $boardRepository
    ) {
        $this->blockFactory = $widgetFactory;
        $this->boardRepository = $boardRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(BoardInterface $board)
    {
        $result = [];

        foreach ($board->getBlocks() as $data) {
            $result[] = $this->initFromArray($data);
        }

        return $result;
    }

    public function create()
    {
        return $this->blockFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get(BoardInterface $board, $blockId)
    {
        foreach ($this->getList($board) as $block) {
            if ($block->getId() == $blockId) {
                return $block;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     * @todo to heavy
     */
    public function save(BoardInterface $board, BlockInterface $block)
    {
        $list = $this->getList($board);
        $isAdd = true;
        foreach ($list as $idx => $item) {
            if ($item->getId() == $block->getId()) {
                $list[$idx] = $block;
                $isAdd = false;
            }
        }
        if ($isAdd) {
            $list[] = $block;
        }

        $blocks = [];
        foreach ($list as $item) {
            $blocks[] = $item->getData();
        }

        $board->setBlocks($blocks);

        $this->boardRepository->save($board);

        return $block;
    }

    /**
     * @param array $data
     * @return BlockInterface
     */
    public function initFromArray(array $data)
    {
        return $this->blockFactory->create(['data' => $data]);
    }
}