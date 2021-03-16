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
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Api\Service\BoardServiceInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Board extends Action
{
    /**
     * @var BoardRepositoryInterface
     */
    protected $boardRepository;

    /**
     * @var BoardServiceInterface
     */
    protected $boardService;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        BoardServiceInterface $boardService,
        Registry $registry,
        Context $context
    ) {
        $this->boardRepository = $boardRepository;
        $this->boardService = $boardService;

        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Dashboard\Api\Data\BoardInterface
     */
    protected function getBoard()
    {
        $board = $this->boardRepository->create();

        if ($this->getRequest()->getParam(BoardInterface::ID)) {
            $board = $this->boardRepository->get($this->getRequest()->getParam(BoardInterface::ID));
        }

        return $board;
    }

    /**
     * @return array
     */
    protected function getBoardsArray()
    {
        die(__METHOD__);
        $boards = [];

        foreach ($this->boardService->getAllowedBoards() as $board) {
            $boards[] = $this->boardService->toArray($board);
        }

        return $boards;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}
