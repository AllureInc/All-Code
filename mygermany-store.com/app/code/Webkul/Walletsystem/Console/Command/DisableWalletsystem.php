<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableWalletsystem
 */
class DisableWalletsystem extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $_modStatus;

    protected $productRepository;

    protected $_appState;


    /**
     * @param \Magento\Eav\Setup\EavSetupFactory        $eavSetupFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager         $moduleManager
     * @param \Magento\Framework\Module\Status          $modStatus
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appstate
    ) {
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->_appState = $appstate;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('walletsystem:disable')
            ->setDescription('Walletsystem Disable Command');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->_moduleManager->isEnabled('Webkul_Walletsystem')) {
            $connection = $this->_resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

            // drop custom tables
            $connection->dropTable($connection->getTableName('wk_ws_credit_rules'));
            $connection->dropTable($connection->getTableName('wk_ws_credit_amount'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_record'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_transaction'));
            
            // delete wallet_credit_based_on product attribute
            $this->_eavAttribute->loadByCode('catalog_product', 'wallet_credit_based_on')->delete();
            // delete wallet_cash_back product attribute
            $this->_eavAttribute->loadByCode('catalog_product', 'wallet_cash_back')->delete();
            
            // delete product
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            $this->registry->register('isSecureArea', true);
            // using sku
            $this->productRepository->deleteById(\Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU);
            // disable walletsystem module
            $this->_modStatus->setIsEnabled(false, ['Webkul_Walletsystem']);

            // delete entry from setup_module table
            $tableName = $connection->getTableName('setup_module');
            $connection->query("DELETE FROM " . $tableName . " WHERE module = 'Webkul_Walletsystem'");
            $output->writeln('<info>Webkul Walletsystem module has been disabled successfully.</info>');
        }
    }
}
