<?php 
namespace Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer;
use Magento\Framework\DataObject;

class GrossTotalSale extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $artist_id = $row->getId();
        $sale = $this->getArtistGrossSale($artist_id);
        return $this->getCurrencySymbol().number_format($sale, 2);
    }

    public function getCurrencySymbol()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();

        return $currencySymbol;
    }

    public function getArtistGrossSale($artist_id){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $model = $objectManager->create('\Cor\MerchandiseHandling\Model\Merchandise');

        $collection = $model->getCollection()->addFieldToSelect('gross_sale')->addFieldToFilter('artist_id', $artist_id);

        $collection->getSelect()->columns(['total_gross_sale' => new \Zend_Db_Expr('SUM(gross_sale)')]);

        $collection = $collection->getFirstItem();

        $data = $collection->getData();
        if (empty($data)) {
            return 0;
        } else {
            extract($data);
            return ((!empty($total_gross_sale)) ? $total_gross_sale : 0);
        }
    }
}