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



namespace Mirasvit\Dashboard\Service;

use Magento\Framework\App\RequestInterface;
use Mirasvit\Dashboard\Api\Service\BoardServiceInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Service\WidgetServiceInterface;
use Mirasvit\Dashboard\Api\Repository\WidgetRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Magento\Backend\Model\Auth\Session;

class BoardService implements BoardServiceInterface
{
    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        Session $authSession,
        RequestInterface $request
    ) {
        $this->boardRepository = $boardRepository;
        $this->authSession = $authSession;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedBoards()
    {
        $userId = $this->authSession->getUser() ? $this->authSession->getUser()->getId() : 0;

        return $this->boardRepository->getCollection()
            ->addFieldToFilter(
                [BoardInterface::TYPE, BoardInterface::USER_ID],
                [BoardInterface::TYPE_SHARED, $userId]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBoard()
    {
        $defaultBoardId = false;

        if ($this->request->getModuleName() !== 'dashboard') {
            $defaultBoard = $this->getAllowedBoards()
                ->addFilter(BoardInterface::IDENTIFIER, $this->request->getModuleName())
                ->getFirstItem();
        } else {
            $defaultBoard = $this->getAllowedBoards()
                ->setOrder(BoardInterface::IS_DEFAULT)
                ->getFirstItem();
        }

        if ($defaultBoard) {
            $defaultBoardId = $defaultBoard->getId();
        }

        return $defaultBoardId;
    }
}