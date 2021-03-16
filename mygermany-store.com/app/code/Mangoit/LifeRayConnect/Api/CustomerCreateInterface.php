<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface CustomerCreateInterface
{
    /**
     * Create new customer in Magento
     *
     * @api
     * @param string $firstname is the firstname of the customer
     * @param string $lastname is the lastname of the customer.
     * @param string $email is the email of the customer.
     * @param string $password is the password of the customer.
     * @return string success or failure message
     */
    public function createcustomer($firstname, $lastname, $email, $password);
}