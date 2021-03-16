<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Test;

/**
 * Class Test
 * @package Scommerce\CookiePopup\Controller\Test
 */
class Test extends \Magento\Framework\App\Action\Action
{
    private $indexerFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
    ) {
        $this->indexerFactory = $indexerFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $idx = $this->indexerFactory->create()->load('catalog_product_category');
        //echo get_class($idx);
        if ($idx->isScheduled()) {
            echo 'scheduled';
        }
        else echo 'manual';
    }
}
