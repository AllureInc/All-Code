<?php
/**
 * Module: Cor_MerchandiseHandling
 * Backend Save Controller
 * Save additional details for product.
 */
namespace Cor\MerchandiseHandling\Controller\Adminhtml\Merchandise;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $productData = $this->getRequest()->getParam('product');
        if ($productData) {
            foreach ($productData as $productItem => $product) {
                if($this->saveData($product) == false)
                {
                    $this->messageManager->addError(__('Error while saving the details.'));
                    $this->_redirect('*/*/');
                }
            }
        }
        $this->messageManager->addSuccess(__('Product mechandise details have been saved.'));
        $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(true);
        $this->_redirect('*/*/');
    }

    /**
     * Method for saving the data into database
     * @return boolean 
     */
    public function saveData($productData)
    {
        if ($productData) {
            foreach($productData as $product) {
                $parent_id = $product['parent_id'];
                $product_id = $product['product_id'];
                $product_sku = $product['product_sku'];
                $count_in = $product['count_in'];
                $count_out = $product['count_out'];
                $add_on = $product['add_on'];
                $total_sold = $product['total_sold'];
                $gross_sale = $product['gross_sale'];
                $total = $product['total'];
                $purchase_order = $product['purchase_order'];
                $event_id = $product['event_id'];
                $artist_id = $product['artist_id'];
                $comp = $product['comp'];

                $model = $this->_objectManager->create('Cor\MerchandiseHandling\Model\Merchandise');

                $collection = $model->getCollection()
                    ->addFieldToFilter('event_id', array('eq' => $event_id))
                    ->addFieldToFilter('artist_id', array('eq' => $artist_id))
                    ->addFieldToFilter('product_id', array('eq' => $product_id))
                    ->addFieldToFilter('product_sku', array('eq' => $product_sku))
                    ->addFieldToFilter('product_parent_id', array('eq' => $parent_id));

                if ($purchase_order != 0) {
                    if (count($collection->getData()) > 0) {
                        foreach($collection->getData() as $item) {
                            $model->load($item['id']);

                            $countOld = $model->getCountIn();
                            $addOnOld = $model->getAddOn();

                            $model->setEventId($event_id);
                            $model->setArtistId($artist_id);
                            $model->setPurchaseOrder($purchase_order);
                            $model->setCountIn($count_in);
                            $model->setAddOn($add_on);
                            $model->setTotal($total);
                            $model->setComp($comp);
                            $model->setCountOut($count_out);
                            $model->setTotalSold($total_sold);
                            $model->setGrossSale($gross_sale);
                            $model->save();
                            /* update product inventory */
                            $this->updateProductQty($product_id, $countOld, $count_in, $addOnOld, $add_on, 0);
                            /* update product inventory */
                            if (!$model->save()) {
                                return false;
                            }
                        }
                    } else {
                        $data = [
                            'event_id'=> $event_id,
                            'artist_id'=> $artist_id,
                            'product_parent_id'=> $parent_id,
                            'product_id'=> $product_id,
                            'product_sku'=> $product_sku,
                            'purchase_order'=> $purchase_order,
                            'count_in'=> $count_in,
                            'add_on'=> $add_on,
                            'total'=> $total,
                            'comp'=> $comp,
                            'count_out'=> $count_out,
                            'total_sold'=> $total_sold,
                            'gross_sale'=> $gross_sale
                        ];
                        $model->setData($data);
                        /* update product inventory */
                        $countOld = 0;
                        $addOnOld = 0;
                        $this->updateProductQty($product_id, $countOld, $count_in, $addOnOld, $add_on, 0);
                        /* update product inventory */
                        if (!$model->save()) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function updateProductQty($product_id, $countOld = 0, $countNew = 0, $addOld = 0, $addNew = 0, $newEntry = 0){
        if ($countNew) {
            $model = $this->_objectManager->create('\Magento\CatalogInventory\Model\Stock\Item');
            $inventory = $model->load($product_id, 'product_id');
            $qty = $inventory->getQty();
            if ($newEntry) {
                $newQty = $qty + $countNew + $addNew;
            } else {
                $actualQty = $qty - $countOld;
                $actualQty = $actualQty - $addOld;
                $newQty = $actualQty + $countNew + $addNew;
            }

            $is_in_stock = 0;
            if ($newQty > 0) {
                $is_in_stock = 1;
            }

            $inventory->setQty($newQty);
            $inventory->setIsInStock($is_in_stock);
            $inventory->save();
        }
    }
}
