<?php 
namespace Cor\Artist\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void cor_artist
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table_name = 'cor_artist';
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'first_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'First Name',
                    'after' => 'artist_id',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'last_name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'Last Name',
                    'after' => 'first_name',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'email_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'Artist Email Id',
                    'after' => 'last_name',
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'username',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '256',
                    'comment' => 'Artist Username',
                    'after' => 'last_name',
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table_name = 'cor_artist';
            $installer->getConnection()->changeColumn(
                $installer->getTable($table_name),
                'artist_rep_number',
                'artist_rep_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '255',
                    'comment' => 'Artist rep. number',
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $table_name = 'cor_artist';
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'artist_id');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'first_name');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'last_name');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'username');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'email_id');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'venue_merch_cut');
            $installer->getConnection()->dropColumn($installer->getTable($table_name), 'venue_music_cut');
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $table_name = 'cor_artist';
            $installer->getConnection()->addColumn(
                $installer->getTable($table_name),
                'most_recent_settlement_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '100',
                    'comment' => 'Most Recent Settlement Date',
                ]
            );
            $installer->endSetup();
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $table_name = 'cor_artist';
            $installer->getConnection()->changeColumn(
                $installer->getTable($table_name),
                'artist_tax_id',
                'artist_tax_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'length' => '255',
                    'comment' => 'Artist tax id',
                ]
            );
            $installer->endSetup();
        }
    }
}

