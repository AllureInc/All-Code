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



namespace Mirasvit\Dashboard\Repository\Email;

use Mirasvit\Dashboard\Api\Repository\BlockRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Api\Service\BlockServiceInterface;
use Mirasvit\Dashboard\Api\Data\BlockInterface;

class BlockRepository implements \Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface
{
    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var BlockServiceInterface
     */
    private $blockService;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        BlockRepositoryInterface $blockRepository,
        BlockServiceInterface $blockService
    ) {
        $this->boardRepository = $boardRepository;
        $this->blockRepository = $blockRepository;
        $this->blockService = $blockService;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        $blocks = [];
        $boards = $this->boardRepository->getCollection();
        foreach ($boards as $board) {
            foreach ($this->blockRepository->getList($board) as $block) {
                $index = implode(':', [$board->getId(), $block->getId()]);
                $blocks[$index] = __('Dashboard: %1 - %2', $board->getTitle(), $block->getTitle());
            }
        }

        return $blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($identifier, $data)
    {
        list($boardId, $blockId) = explode(':', $identifier);

        $board = $this->boardRepository->get($boardId);
        $block = $this->blockRepository->get($board, $blockId);

        // override time range
        $time = $block->getData('time');
        if (!is_array($time)) {
            $time = [];
        }

        $time['override'] = true;
        $time['range'] = $data['timeRange'];
        $block->setData('time', $time);

        $data = $this->blockService->getData($block);

        if ($data['type'] == 'Mirasvit\ReportApi\Api\ResponseInterface') {
            if (isset($data['totals']['formatted_data']['value'])) {
                return $this->renderSingle($block, $data);
            } else {
                return $this->renderTable($block, $data);
            }
        }

        return null;
    }

    private function renderTable(BlockInterface $block, $data)
    {
        $rows = [];
        foreach ($data['columns'] as $column) {
            $rows['header'][] = $column['label'];
        }


        foreach ($data['items'] as $idx => $item) {
            foreach ($item['formatted_data'] as $key => $value) {
                $rows[$idx][] = $value;
            }
        }

        foreach ($data['totals']['formatted_data'] as $key => $value) {
            $rows['footer'][] = $value;
        }

        $table = '<table>';
        foreach ($rows as $idx => $row) {
            $table .= '<tr>';
            foreach ($row as $column) {
                if ($idx === 'header' || $idx === 'footer') {
                    $table .= '<th>' . $column . '</th>';
                } else {
                    $table .= '<td>' . $column . '</td>';
                }
            }
            $table .= '</tr>';
        }

        $table .= '</table>';

        $name = $block->getTitle();

        return "
            <h2>{$name}</h2>

            <div class='table-wrapper'>$table</div>
        ";
    }

    private function renderSingle(BlockInterface $block, $data)
    {
        $value = $data['totals']['formatted_data']['value'];

        return "
            <h2>{$block->getTitle()}</h2>

            <div class='value-wrapper'>$value</div>
        ";
    }
}
