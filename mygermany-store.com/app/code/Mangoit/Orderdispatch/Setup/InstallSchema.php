<?php
/**
 * Mangoit  Software.
 *
 * @category  Mangoit 
 * @package   Mangoit
 * @author    Mangoit 
 */

namespace Mangoit\Orderdispatch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $status = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order\Status');

        $status->setData('status', 'compliance_check')->setData('label', 'Compliance Check')->save();
        $status->assignState('compliance_check', true, true);


        $status->setData('status', 'order_processed')->setData('label', 'Order Processed')->unsetData('id')->save();
        $status->assignState('order_processed', true, true);


        $status->setData('status', 'sent_to_mygermany')->setData('label', 'Sent to myGermany')->unsetData('id')->save();
        $status->assignState('sent_to_mygermany', true, true);

        $status->setData('status', 'received')->setData('label', 'Received')->unsetData('id')->save();
        $status->assignState('received', true, true);

        $status->setData('status', 'order_verified')->setData('label', 'Order Verified')->unsetData('id')->save();
        $status->assignState('order_verified', true, true);


        $installer->endSetup();
    }
}
