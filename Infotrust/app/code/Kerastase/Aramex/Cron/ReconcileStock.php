<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 12:09 PM
 */

namespace Kerastase\Aramex\Cron;

use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class ReconcileStock
{
    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    protected $helper;

    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\History
     */
    private $history;

    protected $stockItem;

    protected $stockRegistry;

    public function __construct(
        \Kerastase\Aramex\Helper\Data $helper,
        \Kerastase\Aramex\Logger\Logger $logger,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Kerastase\Aramex\Model\ResourceModel\History $history,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItem,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->product = $product;
        $this->stockItem = $stockItem;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->history = $history;
    }

    public function execute()
    {
        try {
            $pathToStockFiles = $this->helper->getStockAramexFolder();
            $files = $this->helper->getAllFiles($pathToStockFiles);

            foreach ($files as $file) {
                $order = $this->helper->readXMLFile($pathToStockFiles.'/'.$file, 'STOCK');
                $lines = $order['LINEITEM'];
                $counter = 0;
                $historyDate=date('Y-m-d H:i:s');
                foreach ($order as $lineitem) {
                    if (strlen(json_encode($lineitem['SKU'])) > 0) {
                        $isArray = false;
                    }else{
                        $isArray = true;
                    }
                }
                $this->logger->info('TIMES', array($counter));
                if ($counter === 0) {
                    $this->logger->error('Error when opening the requested file!');
                } else {
                    if ($isArray === false) {
                        $sku = strval($lines['SKU']);
                        $productId = $this->product->getIdBySku($sku);
                        if ($productId) {
                            $productStock = $this->stockItem->get($productId);
                            $old_qty= $productStock->getQty();
                            //if ($productStock->getQty() >= $lines['QTYORDERABLE']) {
                            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                            $stockItem->setQty($lines['QTYORDERABLE']);
                            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                            $comment =  "Product Quantity Successfully Updated";
                            $this->history->addRecord($sku,$old_qty, $lines['QTYORDERABLE'], $comment, $historyDate);

                        } else {
                            $this->logger->info('##  Product with SKU '.$sku .' does not exist');
                            $comment = "Product  does not exist";
                            $this->history->addRecord($sku,0,$lines['QTYORDERABLE'] ,$comment, $historyDate);
                        }
                    } else {
                        foreach ($lines as $line) {
                            $sku = strval($line['SKU']);
                            $productId = $this->product->getIdBySku($sku);
                            if ($productId) {
                                $productStock = $this->stockItem->get($productId);
                                $old_qty= $productStock->getQty();
                                // if ($productStock->getQty() >= $line['QTYORDERABLE']) {
                                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                                $stockItem->setQty($line['QTYORDERABLE']);
                                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                                $comment =  "Product Quantity Successfully Updated";
                                $this->history->addRecord($sku,$old_qty, $line['QTYORDERABLE'], $comment, $historyDate);

                            } else {
                                $this->logger->info('##  Product with SKU '.$sku .' does not exist');
                                $comment ="Product does not exist";
                                $this->history->addRecord($sku,0,$line['QTYORDERABLE'] ,$comment, $historyDate);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
    }
}
