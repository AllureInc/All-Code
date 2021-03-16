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

use Mirasvit\Dashboard\Controller\Adminhtml\Board;

class Index extends Board
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        die(__METHOD__);
        try {
            $board = $this->getBoard();
            $result = [
                'success' => true,
                'board'   => $this->boardService->toArray($board),
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response->representJson(json_encode($result));
    }
}
