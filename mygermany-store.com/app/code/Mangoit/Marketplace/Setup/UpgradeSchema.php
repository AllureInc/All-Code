<?php 
namespace Mangoit\Marketplace\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup2
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup2 = $setup2;
    }


    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup(); 
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'shop_font',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Vendor Shop google fonts'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'shop_background_img',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Vendor Shop background image'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            // creating customer attributes
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerSetup = $objectManager->create('Mangoit\Marketplace\Setup\CustomerSetup');
                $customerSetup->installCustomerAttributes();
            // creating customer attributes
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'is_profile_approved',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Is profile approved: 1 approved 0 disapproved '
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'product_watermark_image',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Product watermark images'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'watermark_opacity',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Watermark images opacity'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'watermark_image_size',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Watermark images size'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'watermark_image_position',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Watermark image positions'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'account_deactivate',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Account Deactivate will work as a global'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            // removing customer attributes
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerSetup = $objectManager->create('Mangoit\Marketplace\Setup\CustomerSetup');
                $customerSetup->updateCustomerAttributes();
            // removing customer attributes
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup2]);

            $attributeSet = 'Default';
            $attributeSetGroup = 'General';
            $label = 'Shipping Price to mygermany GMBH';

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'shipping_price_to_mygmbh',
                [
                    'type'    => 'decimal',
                    'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                    'label'   => $label,
                    'group' => $attributeSetGroup,
                    'input'   => 'price',
                    'default' => 0,
                    'visible' => true,
                    'required'=> true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'user_defined' => true,
                    'unique' => false,
                    'attribute_set' => 'default',
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]

            );
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup2]);

            $attributeSet = 'Default';
            $attributeSetGroup = 'General';

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mygmbh_shipping_product_length',
                [
                    'type'    => 'decimal',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Weight::class,
                    'input_renderer' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
                    'label'   => 'Length',
                    'group' => $attributeSetGroup,
                    'input'   => 'text',
                    'default' => 0,
                    'visible' => true,
                    'required'=> true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'user_defined' => true,
                    'unique' => false,
                    'attribute_set' => 'default',
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]

            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mygmbh_shipping_product_width',
                [
                    'type'    => 'decimal',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Weight::class,
                    'input_renderer' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
                    'label'   => 'Width',
                    'group' => $attributeSetGroup,
                    'input'   => 'text',
                    'default' => 0,
                    'visible' => true,
                    'required'=> true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'user_defined' => true,
                    'unique' => false,
                    'attribute_set' => 'default',
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]

            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mygmbh_shipping_product_height',
                [
                    'type'    => 'decimal',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Weight::class,
                    'input_renderer' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight::class,
                    'label'   => 'Height',
                    'group' => $attributeSetGroup,
                    'input'   => 'text',
                    'default' => 0,
                    'visible' => true,
                    'required'=> true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'user_defined' => true,
                    'unique' => false,
                    'attribute_set' => 'default',
                    'visible_on_front' => false,
                    'used_in_product_listing' => true
                ]

            );
        }
        if (version_compare($context->getVersion(), '1.0.10') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'profile_admin_notification',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Seller profile notification flag for admin'
                ]
            );
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'trustworthy',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'trustworthy Seller 1 otherwise 0'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'website_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Vendor Website URL'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.11') < 0) {
            $marketplace_userdata = 'marketplace_userdata';
    

            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'generate_invoice',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Generate Invoices Weekly/Monthly.'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_userdata),
                'become_seller_request',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => '0 no request, 1 requested'
                ]
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.12') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                'vendor_to_mygermany_cost',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'Vendor to myGermany cost'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                'scc_cost',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => 255,
                    'comment'   => 'SCC cost'
                )
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.13') < 0) {
            /*
             * Create table 'mangoit_sensitive_attributes'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mangoit_sensitive_attributes'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'mageproduct_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Magento Product ID'
                )
                ->addColumn(
                    'sensitive_attributes',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    NULL,
                    ['unsigned' => true, 'nullable' => false, 'default' => NULL],
                    'Sensitive attributes'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )
                ->setComment('Mangoit Sensitive Attributes');
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.14') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup2]);
            $attributeSet = 'Default';
            $attributeSetGroup = 'General';
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'delivery_days_from',
                [   
                    'attribute_set' =>  $attributeSet,
                    'group' => $attributeSetGroup,
                    'label' => 'Delivery days from',
                    'type' => 'int',
                    'source' => 'Mangoit\Marketplace\Model\Attribute\Source\Deliverydaysfrom',
                    'backend' => '',
                    'frontend' => '',
                    'input' => 'select',
                    'class' => '',
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]

            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'delivery_days_to',
                [   
                    'attribute_set' =>  $attributeSet,
                    'group' => $attributeSetGroup,
                    'label' => 'Delivery days to',
                    'type' => 'int',
                    'source' => 'Mangoit\Marketplace\Model\Attribute\Source\Deliverydaysto',
                    'backend' => '',
                    'frontend' => '',
                    'input' => 'select',
                    'class' => '',
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]

            );
        }
        if (version_compare($context->getVersion(), '1.0.15') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                'vendor_delivery_days',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => '',
                    'comment'   => 'Vendor product delivery days'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'vendor_delivery_days',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default'  => NULL,
                    'length'    => '',
                    'comment'   => 'Vendor product delivery days'
                )
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('quote_item'),
                'fsk_product',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'FSK product: 1 Yes, 0 No'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_item'),
                'fsk_product',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'FSK product: 1 Yes, 0 No'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_invoice_item'),
                'fsk_product',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'FSK product: 1 Yes, 0 No'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_shipment_item'),
                'fsk_product',
                 [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'FSK product: 1 Yes, 0 No'
                ]
            );
            $installer->endSetup();
        }

        /*if (version_compare($context->getVersion(), '1.0.16') < 0) {

            $installer->startSetup();

            $status = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order\Status');

            $status->setData('status', 'return_by_customer')->setData('label', 'Returned by Customer')->save();
            $status->assignState('return_by_customer', true, true);

            $status->setData('status', 'return_by_mygermany')->setData('label', 'Returned by myGermany')->unsetData('id')->save();
            $status->assignState('return_by_mygermany', true, true);

            $installer->endSetup();
        }*/
        
        if (version_compare($context->getVersion(), '1.0.17') < 0) {
            $marketplace_saleslist = 'marketplace_saleslist';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_saleslist),
                'return_request_by_customer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Return request from the customer 0 or 1'
                ]
            );
            $installer->endSetup();
        }
        
        if (version_compare($context->getVersion(), '1.0.18') < 0) {
            $marketplace_saleslist = 'marketplace_saleslist';
    
            $installer->getConnection()->addColumn(
                $installer->getTable($marketplace_saleslist),
                'is_payment_returned',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Pyament returned by vendor to admin 0 or 1'
                ]
            );
            $installer->endSetup();
        }
    }
}