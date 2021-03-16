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



namespace Mirasvit\Dashboard\Ui\Block\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Data\WidgetInterface;
use Mirasvit\Dashboard\Api\Repository\BlockRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\WidgetRepositoryInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Mirasvit\Dashboard\Api\Service\WidgetServiceInterface;
use Magento\Ui\Config\DataFactory as DataInterfaceFactory;

class DataProvider extends AbstractDataProvider
{
    const TIME_COMPARISON_DISABLED = 'off';

    /**
     * @var int
     */
    private $blockId;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var ModifierInterface[]
     */
    private $modifiers;

    public function __construct(
        RequestInterface $request,
        BoardRepositoryInterface $boardRepository,
        BlockRepositoryInterface $blockRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $modifiers = [],
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->boardRepository = $boardRepository;
        $this->blockRepository = $blockRepository;
        $this->modifiers = $modifiers;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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
     * @param UiComponentInterface $component
     * @return array
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $child) {
            $data['children'][$child->getName()] = $this->prepareComponent($child);
        }

        $component->prepare();

        $data['arguments']['data'] = $component->getData();
        if (!isset($data['arguments']['data']['config']['formElement'])) {
            $data['arguments']['data']['config']['formElement'] = $component->getComponentName();
        }
        if (!isset($data['arguments']['data']['config']['componentType'])) {
            $data['arguments']['data']['config']['componentType'] = 'field';
        }

        return $data;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->blockId = $filter->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $board = $this->boardRepository->get($this->request->getParam(BoardInterface::ID));
        if (!$board) {
            $board = $this->boardRepository->create();
        }

        $block = $this->blockRepository->get($board, $this->blockId);

        if (!$block) {
            $block = $this->blockRepository->create();
            $block->setId($this->blockId);
        }

        $blockData = $block->asArray();
        $blockData[BoardInterface::ID] = $board->getId();

        foreach ($this->modifiers as $modifier) {
            $blockData = $modifier->modifyData($blockData);
        }

        return [$this->blockId => $blockData];
    }
}
