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

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Dashboard\Model\BoardFactory;
use Mirasvit\Dashboard\Model\ResourceModel\Board\CollectionFactory as BoardCollectionFactory;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;

class BoardRepository implements BoardRepositoryInterface
{
    /**
     * @var BoardFactory
     */
    private $boardFactory;

    /**
     * @var BoardCollectionFactory
     */
    private $boardCollectionFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        BoardFactory $boardFactory,
        BoardCollectionFactory $boardCollectionFactory,
        EntityManager $entityManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->boardFactory = $boardFactory;
        $this->boardCollectionFactory = $boardCollectionFactory;
        $this->entityManager = $entityManager;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->boardCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($boardId)
    {
        $board = $this->create();

        $this->entityManager->load($board, $boardId);

        if (!$board->getId()) {
            return false;
        }

        return $board;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->boardFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BoardInterface $board)
    {
        $this->entityManager->delete($board);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(BoardInterface $board)
    {
        // This prevents error "Area code is not set", when we create board during installation
        if ($board->getType() == BoardInterface::TYPE_PRIVATE) {
            /** @var \Magento\Backend\Model\Auth\Session $session */
            $session = $this->objectManager->get('Magento\Backend\Model\Auth\Session');
            if ($session->getUser()) {
                $board->setUserId($session->getUser()->getId());
            }
        }

        if (!$board->getMobileToken()) {
            $board->setMobileToken(md5(microtime(true)));
        }

        if (!$board->getIdentifier()) {
            $board->setIdentifier(md5(microtime(true)));
        }

        if (!$board->getType()) {
            $board->setType(BoardInterface::TYPE_SHARED);
        }

        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $board->setUpdatedAt($dateTime->gmtDate());
        if ($board->isObjectNew() || !$board->hasData(BoardInterface::CREATED_AT)) {
            $board->setCreatedAt($dateTime->gmtDate());
        }

        $this->entityManager->save($board);

        return $board;
    }
}