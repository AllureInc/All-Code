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



namespace Mirasvit\Dashboard\Controller\Adminhtml\Block;

use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\WidgetInterface;
use Mirasvit\Dashboard\Controller\Adminhtml\Block;

class Save extends Block
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $block = $this->blockRepository->create();

            $data = $this->getRequest()->getParams();

            $block
                ->setId($data[BlockInterface::ID])
                ->setTitle($data[BlockInterface::TITLE]);

            foreach ($this->omitData($data) as $key => $value) {
                $value ? $block->setData($key, $value) : $block->unsetData($key);

            }

            $result = [
                'success' => true,
                'block'   => $block->asArray(),
            ];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode($result));
    }

    /**
     * Omit block constants and also remove empty data
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
            if (in_array($key, ['key', 'isAjax', 'form_key', 'board_id'], true)) {
                continue;
            }

            if (in_array($key,
                [BlockInterface::ID, BlockInterface::SIZE, BlockInterface::POS, BlockInterface::TITLE], true)
            ) {
                continue;
            }

            $value = $this->omitData($value);

            if ($value !== "") {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
