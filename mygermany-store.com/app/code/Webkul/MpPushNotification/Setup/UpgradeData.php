<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

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
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->eavSetupFactory = $eavSetupFactory;
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
         * insert MpSellerBuyerCommunication controller's data
         */
        $data = [];
        if (empty($this->controllersRepository->getByPath('mppushnotification/users/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPushNotification',
                'controller_path' => 'mppushnotification/users/index',
                'label' => 'Registered Users',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        
        if (empty($this->controllersRepository->getByPath('mppushnotification/templates/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPushNotification',
                'controller_path' => 'mppushnotification/templates/index',
                'label' => 'Notification Templates',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!empty($data)) {
            $setup->getConnection()
                ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);
        }

        $setup->endSetup();
    }
}
