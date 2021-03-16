<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model Class
 * For retrieving customer's data
 */
namespace Cnnb\Gtm\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;

class Customer extends DataObject
{

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * Customer constructor.
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($data);
    }

    /**
     * Get Customer array
     *
     * @return array
     */
    public function getCustomer()
    {
        $isLoggedIn = $this->getCustomerSession()->isLoggedIn();
        $data = [
            'isLoggedIn' => $isLoggedIn,
        ];

        if ($isLoggedIn) {
            $data['id'] = $this->getCustomerSession()->getCustomerId();
            $data['groupId'] = $this->getCustomerSession()->getCustomerGroupId();
        }

        return $data;
    }

    /**
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }
}
