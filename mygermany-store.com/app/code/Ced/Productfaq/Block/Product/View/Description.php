<?php 
namespace Ced\Productfaq\Block\Product\View;
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product;
use Ced\Productfaq\Helper\Data;
use Ced\Productfaq\Model\ProductfaqFactory;
use Ced\Productfaq\Model\LikesFactory;

class Description extends \Magento\Framework\View\Element\Template
{

    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_likes = null;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        ProductfaqFactory $productFactory,
        LikesFactory $likesFactory,
        array $data = []
    ) {
        $this->_productFactory   = $productFactory;
        $this->_coreRegistry = $registry;
        $this->_likes = $likesFactory;
        parent::__construct($context, $data);
    }
    protected  function _construct()
    {
        parent::_construct();
        $_product = $this->getProduct();
        $productid=$_product->getId();
        $collection = $this->_productFactory->create()->getCollection()->addFieldToFilter('product_id', $productid)->addFieldToFilter('is_active', '1')
            ->setOrder('id', 'DESC');
        $this->setCollection($collection);
    }
    
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /**
 * @var \Magento\Theme\Block\Html\Pager 
*/
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'ced.faq.list.pager'
        );
        $pager->setLimit(10)
            ->setShowAmounts(true)
            ->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
    
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
   
    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }
    public function getLikesCount($questionid)
    { 
        $_product = $this->getProduct();
        
        $productid=$_product->getId();
        
        $likes= $this->_likes->create()->getCollection()->addFieldToFilter('question_id', $questionid)->addFieldToFilter('product_id', $productid);
        return $likes;
    }
}