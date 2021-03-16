<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface NewsLetterInterface
{
    /**
     * Newsletter subscribe/unsubscribe from liferay
     *
     * @api
     * @param string $email 
     * @param string $issubscribed user .
     * @return string .
     */
    public function issubscribeuser($email, $issubscribed);
}