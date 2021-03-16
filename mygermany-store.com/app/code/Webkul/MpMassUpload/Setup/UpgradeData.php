<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository
    ) {
        $this->controllersRepository = $controllersRepository;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];

        if (empty($this->controllersRepository->getByPath('mpmassupload/product/view'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/product/view',
                'label' => 'Mass Upload Product',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (empty($this->controllersRepository->getByPath('mpmassupload/product/export'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/product/export',
                'label' => 'MassUpload Product Export',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (empty($this->controllersRepository->getByPath('mpmassupload/dataflow/profile'))) {
            $data[] = [
                'module_name' => 'Webkul_MpMassUpload',
                'controller_path' => 'mpmassupload/dataflow/profile',
                'label' => 'Mass Upload Dataflow Profile',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $setup->endSetup();
    }
}
