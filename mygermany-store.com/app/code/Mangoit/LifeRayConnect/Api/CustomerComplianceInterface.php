<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface CustomerComplianceInterface
{
    /**
     * Update customer compliance status
     *
     * @api
     * @param string $email Email of customer
     * @param string $compliancestatus is the status of the customer.
     * @return string success or failure message
     */
    public function customerupdate($email, $compliancestatus);
}