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



namespace Mirasvit\Dashboard\Controller\Adminhtml\Board;

use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Data\WidgetInterface;
use Mirasvit\Dashboard\Controller\Adminhtml\Board;

class Save extends Board
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $board = $this->getBoard();

            if ($params = $this->getRequest()->getParam('board')) {
                $data = \Zend_Json::decode($params, true);

                $this->updateData($board, $data);

                $this->updateBlocks($board);
            } else if ($data = $this->getRequest()->getParams()) {
                $this->updateData($board, $data);
            }

            $this->boardRepository->save($board);

            $result = [
                'success' => true,
                'board'   => $board->asArray(),
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->representJson(json_encode($result));
    }

    private function updateBlocks(BoardInterface $board)
    {
        $blocks = $board->getBlocks();

        foreach ($blocks as $key => $block) {
            $block = $this->omitData($block);
            $blocks[$key] = $block;
        }

        $board->setBlocks($blocks);

        return $this;
    }

    /**
     * Omit block contstant and also remove empty data
     *
     * @param array|string $data
     * @return array|string|number
     */
    private function omitData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $value = $this->omitData($value);

            if ($value !== "") {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Update board data.
     *
     * @param BoardInterface $board
     * @param string[]       $data
     */
    private function updateData(BoardInterface $board, array $data = [])
    {
        foreach ($data as $key => $value) {
            $board->setDataUsingMethod($key, $value);
        }
    }
}
