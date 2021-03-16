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



namespace Mirasvit\Dashboard\Ui\Block\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Dashboard\Api\Repository\RendererRepositoryInterface;

class Renderer implements ArrayInterface
{
    private $rendererRepository;

    public function __construct(
        RendererRepositoryInterface $rendererRepository
    ) {
        $this->rendererRepository = $rendererRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $data = [];
        foreach ($this->rendererRepository->getList() as $renderer) {
            $data[] = [
                'label' => $renderer->getName(),
                'value' => $renderer->getIdentifier(),
            ];
        }

        return $data;
    }
}
