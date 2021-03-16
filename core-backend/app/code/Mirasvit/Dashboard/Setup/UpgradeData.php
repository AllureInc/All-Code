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


namespace Mirasvit\Dashboard\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;
use Mirasvit\Dashboard\Renderer\SingleRenderer;
use Mirasvit\Dashboard\Renderer\TableRenderer;
use Magento\Framework\App\State;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;
    /**
     * @var State
     */
    private $appState;

    public function __construct(State $appState, BoardRepositoryInterface $boardRepository)
    {
        $this->boardRepository = $boardRepository;
        $this->appState = $appState;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        } catch (\Exception $e) {}

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->updateBlocks();
        }

        $setup->endSetup();
    }

    /**
     * Update blocks data.
     */
    private function updateBlocks()
    {
        foreach ($this->boardRepository->getCollection() as $board) {
            $blocks = $board->getBlocks();
            foreach ($blocks as $blockKey => $block) {
                if (isset($block['renderer'])) {
                    if ($block['renderer'] === TableRenderer::ID && !isset($block['report']['data']['report'])) {
                        $blocks[$blockKey]['report']['data']['report'] = 'order_overview';
                        $blocks[$blockKey]['report']['data']['columns'] = '';
                    } elseif ($block['renderer'] === SingleRenderer::ID) {
                        if (isset($block['report']['data']) && !isset($block['metric'])) {
                            // restore values from previous module's version
                            if (is_string($block['report']['data'])) {
                                $blocks[$blockKey]['metric'] = ['data' => $block['report']['data']];
                            } elseif (is_array($block['report']['data'])) {
                                $blocks[$blockKey]['metric'] = ['data' => array_shift($block['report']['data'])];
                            }
                        } else {
                            $blocks[$blockKey]['report']['data']['report'] = '';
                        }
                    }
                }
            }

            $board->setBlocks($blocks);
            $this->boardRepository->save($board);
        }
    }
}
