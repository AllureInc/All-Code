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



namespace Mirasvit\Dashboard\Ui\Dashboard;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Service\BoardServiceInterface;
use Magento\Framework\Stdlib\ArrayManager;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var BoardServiceInterface
     */
    private $boardService;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ModifierInterface[]
     */
    private $modifiers;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        BoardServiceInterface $boardService,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        $name = 'dashboard_data_source',
        array $modifiers = [],
        array $meta = [],
        array $data = []
    ) {
        $this->boardService = $boardService;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;

        $this->modifiers = $modifiers;

        parent::__construct($name, 'id', 'id', $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        if (!isset($this->data['config'])) {
            $this->data['config'] = [];
        }

        $this->data['config'] = [
            'getBoardUrl'    => $this->urlBuilder->getUrl('dashboard/board/index'),
            'saveBoardUrl'   => $this->urlBuilder->getUrl('dashboard/board/save'),
            'editBoardUrl'   => $this->urlBuilder->getUrl('dashboard/board/edit'),
            'deleteBoardUrl' => $this->urlBuilder->getUrl('dashboard/board/delete'),
            'getBlockUrl'    => $this->urlBuilder->getUrl('dashboard/block/index'),
            'queryUrl'       => $this->urlBuilder->getUrl('dashboard/block/query'),
        ];

        return parent::getConfigData();
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->modifiers as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $boards = [];

        foreach ($this->boardService->getAllowedBoards() as $board) {
            $boards[] = $board->asArray();
        }

        $data = [
            'boards'         => $boards,
            'defaultBoardId' => $this->boardService->getDefaultBoard(),
        ];

        foreach ($this->modifiers as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }
}
