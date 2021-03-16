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

class Unlike extends Action
{
  
    /*
    * Faq Unlike Action
    * @return like count
    */
    public function execute()
    {
        if($this->_request->isPost()) {
            $questionid = $this->_request->getParam('id');
            $userip=$this->_request->getParam('ip');
            $productid=$this->_request->getParam('productid');
             /*$model = Mage::getModel('productfaq/like')->getCollection()->addFieldToFilter('question_id',$questionid)->addFieldToFilter('user_ip',$userip); 
             foreach ($model as $del)
              {
	             $id=$del->getData('id');
                 $del->setId($id)->delete();
              $result['unlike']='like';
	           }*/
            $test=$this->_objectManager->create('Ced\Productfaq\Model\Likes')->getCollection()->addFieldToFilter('question_id', $questionid)->addFieldToFilter('product_id', $productid);
            foreach ($test as $data)
            {
                $id= $data['id'];
                $count=$data['likes'];
                $ips=$data['user_ip'];
            }
            $ips= unserialize($ips);
        
            if (strpos($ips, $userip) !== false) {
                if (strpos($ips, ',') !== false) {
                    $userip=str_replace($userip.',', "", $ips);
                }
                else 
                {
                    $userip=str_replace($userip, "", $ips);
                }
            }
            $userip=serialize($userip);
            $count--;
            $data = array('likes'=>$count,'user_ip'=>$userip);
            $model=$this->_objectManager->create('Ced\Productfaq\Model\Likes')->load($id)->addData($data);;
            $model->setId($id)->save();
        }
                 $result['count']=$count;
                 $result['unlike']='like';
                    $this->_response->setHeader('Content-type', 'application/json');
                    $this->_response->setBody(json_encode($result));
    }

}
