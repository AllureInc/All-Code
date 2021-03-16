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

class Like extends Action
{
   
      /**
       * Faq Like Action
       *
       * @return like count
       */
      
    public function execute()
    {
        if($this->_request->isPost()) {  
            $questionid = $this->_request->getParam('id');
            $userip=$this->_request->getParam('ip');
            //$userip=serialize($userip);
            $productid=$this->_request->getParam('productid');
            $test=$this->_objectManager->create('Ced\Productfaq\Model\Likes')->getCollection()->addFieldToFilter('question_id', $questionid)->addFieldToFilter('product_id', $productid);
            if(count($test->getData())>0) {
                foreach ($test as $data)
                                   {   
                    $id= $data['id'];
                    $count=$data['likes'];
                    $ips=$data['user_ip'];
                    $ips= unserialize($ips);
                    if($ips) {
                                             $ips .=','.$userip;
                    }
                    else 
                                {
                         $ips .=$userip;
                    }
                    $userip=serialize($ips);
                    $count++;
                    if ($questionid) { 
                         $data = array('question_id'=>$questionid,'product_id'=>$productid,'user_ip'=>$userip,'likes'=>$count);
                         $model=$this->_objectManager->create('Ced\Productfaq\Model\Likes')->load($id)->addData($data);;
                         $model->setId($id)->save();
                         $result['count']=$count;
                         $result['like']='unlike';
                    }
                } 
            }
            else{
                if ($questionid) {
                    $count=1;
                    $userip=serialize($userip);
                    $data = array('question_id'=>$questionid,'product_id'=>$productid,'user_ip'=>$userip,'likes'=>$count);
                    $model=$this->_objectManager->create('Ced\Productfaq\Model\Likes')->setData($data);;
                    $model->save();
                    $result['count']=$count;
                    $result['like']='unlike';
                }
            }
        } 
        
        $this->_response->setHeader('Content-type', 'application/json');
        $this->_response->setBody(json_encode($result));
    }
    
}

