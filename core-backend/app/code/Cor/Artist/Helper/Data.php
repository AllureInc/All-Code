<?php
namespace Cor\Artist\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(        
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * Method for get role id of Artist type user 
     * @return roleId
     */
    public function getRoleId()
    {
        $roleModel = $this->_objectManager->create('Magento\Authorization\Model\Role');
        $roleModel->load('Artist', 'role_name');
        $roleId = $roleModel->getRoleId();
        return $roleId;
    }
}