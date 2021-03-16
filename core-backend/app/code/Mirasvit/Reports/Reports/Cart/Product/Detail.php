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
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Reports\Reports\Cart\Product;

use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\Reports\Model\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Detail extends AbstractReport
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Config $config,
        Context $context
    ) {
        $this->productRepository = $productRepository;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (isset($_GET['filters']['catalog_product_entity|entity_id']['from'])) {
            $productId = $_GET['filters']['catalog_product_entity|entity_id']['from'];
            $product = $this->productRepository->getById($productId);

            $name = $product->getName();

            return __('Product Quotes / %1', $name);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('catalog_product_entity');

        $this->addFastFilters([
            'quote|created_at__day',
            'quote|store_id',
        ]);

        $this->setRequiredColumns([
            'catalog_product_entity|entity_id',
        ]);

        $this->setDefaultColumns([
            'quote_item|qty__sum',
        ]);

        $this->setDefaultDimension(
            'quote|created_at__day'
        );

        $this->addDimensions([
            'quote|created_at__day',
            'quote|created_at__week',
            'quote|created_at__month',
            'quote|created_at__quarter',
            'quote|created_at__year',
        ]);

        $this->addColumns($this->context->getProvider()->getComplexColumns('quote_item'));
        $this->addColumns($this->context->getProvider()->getSimpleColumns('catalog_product_entity'));

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'quote_item|qty__sum',
            ]);
    }
}
