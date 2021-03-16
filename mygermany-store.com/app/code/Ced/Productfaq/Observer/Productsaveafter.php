<?php
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
namespace Ced\Productfaq\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Productsaveafter implements ObserverInterface
{
    
    protected $request;
    protected $catalogSession;
    protected $_objectManager;
    
    public function __construct(RequestInterface $request,\Magento\Framework\ObjectManagerInterface $ob,\Magento\Framework\Stdlib\DateTime\DateTime $date ,\Magento\Catalog\Model\Session $catalogSession)
    {
    	$this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->_objectManager = $ob;
        $this->date = $date;
    }
    /**
     * Save Faq from product edit page
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();  // you will get product object
        $productid = $product->getId();
        
        
        if($this->catalogSession->getFaqData() != NULL)
        {
        $data=$this->catalogSession->getFaqData()->toArray();
        $title = $data['title'];
    
        $description =  $data['description'];
        $date = $this->date->gmtDate();
   
        for($i=1;$i<=count($title);$i++)
            {
            $titles= $title['quest'.$i];
            $descriptions= $description['desc'.$i];
            $visible='1';
            try
            {
                if($titles) {
                                
                    $model= $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                    $model->setData('product_id', $productid)
                        ->setData('title', $titles)
                        ->setData('is_active', $visible)
                        ->setData('description', $descriptions)
                        ->setData('posted_by', 'admin')
                        ->setData('publish_date', $date);
                                $model->save();
                }
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
      
    }  
    } 
}