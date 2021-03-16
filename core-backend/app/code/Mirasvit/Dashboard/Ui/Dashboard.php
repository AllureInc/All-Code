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



namespace Mirasvit\Dashboard\Ui;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;

class Dashboard extends AbstractComponent
{
    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var BoardInterface
     */
    private $board;

    public function __construct(
        RequestInterface $request,
        BoardRepositoryInterface $boardRepository,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->boardRepository = $boardRepository;

        $data['template'] = 'templates/dashboard';
        $data['spinner'] = 'spinner';

        $this->board = $this->ensureBoard($request->getModuleName());

        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return 'dashboard';
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');

        $config = array_merge($config, [
            'component' => 'Mirasvit_Dashboard/js/dashboard',
            'provider'  => "{$this->getName()}.dashboard_data_source",
            'deps'      => "{$this->getName()}.dashboard_data_source",
        ]);

        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        $data = [
            'data' => $this->getContext()->getDataProvider()->getData(),
        ];

        return $data;
    }

    private function ensureBoard($moduleName)
    {
        $board = $this->boardRepository->getCollection()
            ->addFilter(BoardInterface::IDENTIFIER, $moduleName)
            ->getFirstItem();

        if (!$board->getId()) {
            $board = $this->boardRepository->create()
                ->setIdentifier($moduleName)
                ->setType(BoardInterface::TYPE_SHARED)
                ->setTitle('Dashboard');

            $this->boardRepository->save($board);
        }

        return $board;
    }
}
