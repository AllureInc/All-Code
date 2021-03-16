<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
*/
namespace Solrbridge\Search\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Solrbridge\Search\Model\Indexer as SolrbridgeIndexer;
use Solrbridge\Search\Model\ResourceModel\Index as SolrbridgeIndex;

class ReindexallCommand extends Command
{
    protected $_indexCollection = null;

    protected $_solrbridgeIndex = null;

    protected $_storeManager = null;
    
    protected $_appState;

    public function __construct(
        \Solrbridge\Search\Model\Index $solrbridgeIndex,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        $name = null
    ) {
        $this->_solrbridgeIndex = $solrbridgeIndex;
        $this->_storeManager = $storeManager;
        $this->_appState = $state;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('solrbridge:reindexall')->setDescription('Solrbrige reindex all.');
    }

    protected function _runReindex($index)
    {
        $handlerModel = $index->getDoctypeHandlerModel();
        $indexRunner = $handlerModel->getIndexer()->getIndexRunner();
        $indexRunner->runReindex();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->_appState->getAreaCode()) {
                $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            }
        } catch (\Exception $e) {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
        $indexCollection = $this->_solrbridgeIndex->getCollection();
        foreach ($indexCollection as $indexItem) {
            $this->_runReindex($indexItem);
        }
        $output->writeln('DONE');
    }
}
