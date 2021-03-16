<?php
/**
 * Module: Cor_Productmanagement
 * Backend Block File
 * retrieves events associated with particular artist for showing as options of cor_events product attribute.
 */
namespace Cor\Productmanagement\Block\Adminhtml\Artist;

class Events extends \Magento\Backend\Block\Template
{
    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */
    protected $_objectManager;
    /**
    * @var \Magento\Framework\Registry
    */
    protected $registry;
    /**
    * @var for add template file
    */
    protected $_template = 'Cor_Productmanagement::artist/events.phtml';

    /*
    * Class constructor 
    */
    public function __construct(\Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Framework\Registry $registry)
    {
        parent::__construct($context);
        $this->_objectManager = $objectmanager; 
        $this->registry = $registry;
    }

    /*
    * Construct to set template on product form
    * @return Template 
    */
    protected function _construct()
    {
        $this->setTemplate($this->_template);
    }
}