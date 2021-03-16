<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class InstallData implements InstallDataInterface
{

    protected $_salesSetupFactory;
    protected $_quoteSetupFactory;

    public function __construct(
        ControllersRepository $controllersRepository,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->_salesSetupFactory = $salesSetupFactory;
        $this->_quoteSetupFactory = $quoteSetupFactory;
        $this->controllersRepository = $controllersRepository;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [];
        
        if (!count($this->controllersRepository->getByPath('mpsellercoupons/index/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpSellerCoupons',
                'controller_path' => 'mpsellercoupons/index/index',
                'label' => 'Seller Coupons Manager',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        $setup->getConnection()
              ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);
        $setup->endSetup();
        $salesInstaller = $this->_salesSetupFactory
                        ->create(
                            [
                                'resourceName' => 'sales_setup',
                                'setup' => $setup
                            ]
                        );
        $quoteInstaller = $this->_quoteSetupFactory
                        ->create(
                            [
                                'resourceName' => 'quote_setup',
                                'setup' => $setup
                            ]
                        );

        $this->addQuoteAttributes($quoteInstaller);
        $this->addOrderAttributes($salesInstaller);
    }

    /**
     * add attribute in quote address
     * @param object $installer
     */
    public function addQuoteAttributes($installer)
    {
        $installer->addAttribute('quote_address', 'coupondiscount_total', ['type' => 'text']);
    }

    /**
     * add attribute in sales_order
     * @param object $installer
     */
    public function addOrderAttributes($installer)
    {
        $installer->addAttribute('order', 'coupondiscount_total', ['type' => 'text']);
    }
}
