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



namespace Mirasvit\Dashboard\Ui\Block\Form\Modifier;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Dashboard\Api\Repository\DataSourceRepositoryInterface;
use Mirasvit\Dashboard\Api\Repository\RendererRepositoryInterface;
use Mirasvit\Dashboard\Ui\ComponentTrait;

class DataSourceModifier implements ModifierInterface
{
    use ComponentTrait;

    /**
     * @var DataSourceRepositoryInterface
     */
    private $dataSourceRepository;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @var RendererRepositoryInterface
     */
    private $rendererRepository;

    public function __construct(
        RendererRepositoryInterface $rendererRepository,
        DataSourceRepositoryInterface $dataSourceRepository,
        UiComponentFactory $uiComponentFactory,
        ArrayManager $arrayManager
    ) {
        $this->dataSourceRepository = $dataSourceRepository;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->arrayManager = $arrayManager;
        $this->rendererRepository = $rendererRepository;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        foreach ($this->rendererRepository->getList() as $renderer) {
            $meta = $this->arrayManager->populate("{$renderer->getAlias()}/children/container", $meta);

            foreach ($this->dataSourceRepository->getList() as $source) {
                $identifier = $source->getIdentifier();
                $componentName = "dashboard_datasource_{$identifier}_{$renderer->getAlias()}";

                $component = $this->uiComponentFactory->create($componentName);

                $meta = $this->arrayManager->merge(
                    "{$renderer->getAlias()}/children/container",
                    $meta,
                    $this->prepareComponent($component)
                );
            }
        }

        return $meta;
    }
}