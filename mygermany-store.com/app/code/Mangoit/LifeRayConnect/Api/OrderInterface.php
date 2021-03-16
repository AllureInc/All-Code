<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface OrderInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $id 'all' can be used to specify 'ALL GROUPS'
     * @param string $orderstatus Users name.
     * @return string Greeting message with users name.
     */
    public function updatestatus($id, $orderstatus);
}