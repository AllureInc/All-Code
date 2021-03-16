<?php
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_Twiliosmsnotification
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
namespace Ced\Twiliosmsnotification\Model\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

class Countrycode extends ArraySerialized
{
    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_design = $design;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

     /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $arr   = @unserialize($value);

        if(!is_array($arr)) return '';

        foreach ($arr as $k=>$val) {
            if(!is_array($val)) {
                unset($arr[$k]);
                continue;
            }
        }
        $this->setValue($arr);
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        var_dump($value);die;
        $exixting=array();
        foreach ($value as $key => $val) {
          if($key=='__empty')continue;  
          if(in_array(trim($val['country']),$exixting)){
                unset($value[$key]);
            }else{
                array_push($exixting,trim($val['country']));
            }
        }
        $value = serialize($value);
        $this->setValue($value);
    }
}
