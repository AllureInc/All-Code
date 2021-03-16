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

use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Repository\DataSourceRepositoryInterface;
use Mirasvit\Dashboard\Api\Service\BlockServiceInterface;

class BlockService implements BlockServiceInterface
{
    private $dataSourceRepository;

    public function __construct(
        DataSourceRepositoryInterface $dataSourceRepository
    ) {
        $this->dataSourceRepository = $dataSourceRepository;
    }

    public function getData(BlockInterface $block)
    {
        $dataSource = $this->dataSourceRepository->get($block->getData('dataSource'));

        if (!$dataSource) {
            return false;
        }

        return $dataSource->getData($block);
    }
    //    /**
    //     * @var WidgetTypeRepositoryInterface
    //     */
    //    private $widgetTypeRepository;
    //
    //    public function __construct(
    //        WidgetTypeRepositoryInterface $widgetTypeRepository
    //    ) {
    //        $this->widgetTypeRepository = $widgetTypeRepository;
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function toArray(WidgetInterface $widget)
    //    {
    //        return [
    //            WidgetInterface::ID         => $widget->getId(),
    //            WidgetInterface::TITLE      => $widget->getTitle(),
    //            WidgetInterface::IDENTIFIER => $widget->getIdentifier(),
    //            WidgetInterface::POS        => $widget->getPos(),
    //            WidgetInterface::SIZE       => $widget->getSize(),
    //            WidgetInterface::OPTIONS    => $widget->getOption(),
    //        ];
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getConfigData(WidgetInterface $widget)
    //    {
    //        return $this->getInstance($widget)->getConfigData();
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getData(WidgetInterface $widget)
    //    {
    //        return $this->getInstance($widget)->getData();
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function toEmail(WidgetInterface $widget)
    //    {
    //        return $this->getInstance($widget)->toEmail();
    //    }
    //
    //    /**
    //     * @param WidgetInterface $widget
    //     * @return WidgetInstanceInterface
    //     */
    //    protected function getInstance(WidgetInterface $widget)
    //    {
    //        $identifier = $widget->getIdentifier();
    //
    //        if (!$identifier) {
    //            $identifier = 'text';
    //        }
    //        $model = $this->widgetTypeRepository->get($identifier);
    //        $model->setModel($widget);
    //
    //        return $model;
    //    }
}