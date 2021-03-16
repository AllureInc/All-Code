<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Vendorcommission
 * @author    Mangoit
 */
namespace Mangoit\Vendorcommission\Controller\Adminhtml\Commission;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Setcommission extends Action
{
    protected $_objectManager;
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mangoit_Marketplace::seller');
    }

    public function execute()
    {
        $messageManager = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        $parameters = $this->getRequest()->getParams();
        // echo "<pre>";
        // print_r($parameters);
        // die();
        $attrNameArray = unserialize($parameters['attrarry']);
        // print_r($parameters);
        foreach ($attrNameArray as $value) {
            if (isset($parameters[''.$value])) {
                if (!empty($parameters[''.$value])) {
                    # code...
                    $elec = array_values($parameters[''.$value]);
                    
                    foreach ($elec as $key => $value) {
                        
                        if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $value['commission'])) {
                            echo "commission";
                            exit();
                        }

                        if ($value['to'] != '<') {

                            if (!($value['from'] < $value['to'])) {
                                echo "improper";
                                exit();
                            }    # code...
                        }
                        
                        if ($key > 0) {
                           if (! ($value['from'] > $elec[$key-1]['to'])){
                               echo "from";
                               exit();
                           }
                        }
                    }
                }        
            } 
        }
        /*die();
        if (!empty($parameters['Electronincs'])) {
            # code...
            $elec = array_values($parameters['Electronincs']);
            
            foreach ($elec as $key => $value) {
                
                if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $value['commission'])) {
                    echo "commission";
                    exit();
                }

                if ($value['to'] != '<') {

                    if (!($value['from'] < $value['to'])) {
                        echo "improper";
                        exit();
                    }    # code...
                }
                
                if ($key > 0) {
                   if (! ($value['from'] > $elec[$key-1]['to'])){
                       echo "from";
                       exit();
                   }
                }
            }
        }
        if (!empty($parameters['Non-Electronics'])) {
            # code...
            $nonElec =  array_values($parameters['Non-Electronics']);

            foreach ($nonElec as $key => $value) {

                if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $value['commission'])) {
                    echo "commission";
                    exit();
                }

                if ($value['to'] != '<') {
                    if (!($value['from'] < $value['to'])) {
                        echo "improper";
                        exit();
                    }    # code...
                }
                
                if ($key > 0) {
                   if (! ($value['from'] > $nonElec[$key-1]['to'])){
                       echo "from";
                       exit();
                   }
                }

                if ($key > 0) {
                   if (! ($value['from'] > $nonElec[$key-1]['to'])){
                       echo "from";
                       exit();
                   }
                }
            }
        }*/
        $finalArray = [];
        $electronic = [];
        $nonElectronic = [];
        $fashion = [];
        $commissionObject = $this->_objectManager->create('Mangoit\Vendorcommission\Block\Adminhtml\Customer\Edit\Tab\Commission');
        $attributes = $commissionObject->getAttributesType();
        $customerId = $parameters['customer_id'];
        unset($parameters['customer_id']);
        unset($parameters['isAjax']);
        unset($parameters['key']);
        unset($parameters['attrarry']);
        unset($parameters['form_key']);
        // print_r($parameters);
        // foreach ($parameters as $key => $value) {
        //      str_replace('-', '_', strtolower($key));
        // }

        // die();
        foreach ($parameters as $key => $value) {
            foreach ($value as $newkey => $newvalue) {
                $range = $newvalue['from']."-".$newvalue['to'];
                $electronic[$range] = $newvalue['commission'];
            }
            $finalArray[$key] = $electronic;
            // array_push($finalArray, [$key], [$electronic]);
        }
         /*print_r($finalArray);
         die();

        foreach ($parameters as $key => $value) {
            if ($key == 'Electronincs') {
               foreach ($value as $newkey => $newvalue) {
                   $range = $newvalue['from']."-".$newvalue['to'];
                   // array_push($electronic, array($range => $newvalue['commission']));
                   $electronic[$range] = $newvalue['commission'];

               }
               // $finalArray = array(''.$key =>  $electronic);
               // print_r($finalArray);
            }
            if ($key == 'Non-Electronics') {
               foreach ($value as $newkey => $newvalue) {
                   $range = $newvalue['from']."-".$newvalue['to'];
                   // array_push($nonElectronic, array($range => $newvalue['commission']));
                   $nonElectronic[$range] = $newvalue['commission'];
               }
            }
            if ($key == 'Fashion') {
               foreach ($value as $newkey => $newvalue) {
                   $range = $newvalue['from']."-".$newvalue['to'];
                   // array_push($nonElectronic, array($range => $newvalue['commission']));
                   $fashion[$range] = $newvalue['commission'];
               }
            }
        }
        // die();
        // array_push($finalArray, array('Electronincs'=> $electronic, 'Non-Electronics'=> $nonElectronic));
        $finalArray = array('Electronincs'=> $electronic, 'Non-Electronics'=> $nonElectronic, 'Fashion'=> $fashion);
        print_r($finalArray);
        die();*/
        // echo "<pre>";
        // print_r($finalArray);
        // die();
        $serializeRule = serialize($finalArray);
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
        if (!empty($model->load($customerId, 'seller_id')->getData() ) ) {
            $model->load($customerId, 'seller_id');
            $model->setCommissionRule($serializeRule);
            $model->save();
        } else {
            $data = array('seller_id' => $customerId, 'commission_rule' => $serializeRule);
            $model->setData($data);
            $model->save();
        }
        // $messageManager = $this->_objectManager->create('Magento\Framework\Message\ManagerInterface');
        $messageManager->addSuccessMessage('Commission setting has been saved.');
        echo "true"; 

    }
}
