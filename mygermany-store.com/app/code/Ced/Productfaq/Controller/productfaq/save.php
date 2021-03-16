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
namespace Ced\Productfaq\Controller\Productfaq;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Save extends Action
{
    /*
       * Faq Save Action From Product view page
       * @return like count
       */
    public function execute()
    {
            
        $email=$this->_request ->getParam('email');
        $name=$this->_request ->getParam('name');
           $question=$this->_request ->getParam('question');
           $prdctid=$this->_request ->getParam('product');
           $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate();
           $faq=$this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
           $faq->setData('email_id', $email)
               ->setData('title', $question)
               ->setData('product_id', $prdctid)
               ->setData('is_active', 0)
               ->setData('posted_by', $name)
               ->setData('publish_date', $date);
           $faq->save();
           echo 'Your Question Was Successfully Posted.We will Answer You Shortly';die;
    }
    
}
