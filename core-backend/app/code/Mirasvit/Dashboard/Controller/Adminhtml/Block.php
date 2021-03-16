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



namespace Mirasvit\Dashboard\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\WidgetInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BlockRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\WidgetRepositoryInterface;
use Mirasvit\Dashboard\Api\Service\BlockServiceInterface;
use Mirasvit\Dashboard\Api\Service\BoardServiceInterface;
use Mirasvit\Dashboard\Api\Service\WidgetServiceInterface;
use Magento\Backend\App\Action\Context;

abstract class Block extends Action
{
    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var BlockServiceInterface
     */
    protected $blockService;
    //
    //    /**
    //     * @var WidgetServiceInterface
    //     */
    //    protected $widgetService;
    //
    //    /**
    //     * @var BoardServiceInterface
    //     */
    //    protected $boardService;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        BlockRepositoryInterface $blockRepository,
        BlockServiceInterface $blockService,
        //        WidgetServiceInterface $widgetService,
        //        BoardServiceInterface $boardService,
        Context $context
    ) {
        $this->boardRepository = $boardRepository;
        $this->blockRepository = $blockRepository;
        $this->blockService = $blockService;
        //        $this->widgetService = $widgetService;
        //        $this->boardService = $boardService;

        parent::__construct($context);
    }

    /**
     * @return BoardInterface|false
     */
    protected function getBoard()
    {
        if ($this->getRequest()->getParam(BoardInterface::ID)) {
            return $this->boardRepository->get($this->getRequest()->getParam(BoardInterface::ID));
        }

        return false;
    }

    /**
     * @return BlockInterface
     */
    protected function getBlock()
    {
        $board = $this->getBoard();

        $block = null;

        if ($this->getRequest()->getParam(BlockInterface::ID)) {
            $block = $this->blockRepository->get($board, $this->getRequest()->getParam(BlockInterface::ID));
        }

        return $block ? $block : $this->blockRepository->create();
    }

    //    /**
    //     * @return array
    //     */
    //    protected function getBoardsArray()
    //    {
    //        $boards = [];
    //
    //        foreach ($this->boardService->getAllowedBoards() as $board) {
    //            $boards[] = $this->boardService->toArray($board);
    //        }
    //
    //        return $boards;
    //    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
