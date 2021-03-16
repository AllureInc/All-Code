<?php

namespace Potato\Zendesk\Model\Management;

use Potato\Zendesk\Api\SsoManagementInterface;
use Potato\Zendesk\Model\Config;
use Potato\Zendesk\Lib\JWT\JWT;

class Sso implements SsoManagementInterface
{
    /** @var Config  */
    protected $config;

    /** @var JWT  */
    protected $jwt;

    /**
     * @param JWT $jwt
     * @param Config $config
     */
    public function __construct(
        JWT $jwt,
        Config $config
    ) {
        $this->jwt = $jwt;
        $this->config = $config;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     */
    public function getLocationByCustomer($customer)
    {
        $subdomain = $this->config->getSsoDomain();
        $key = $this->config->getSsoSecretShared();
        $now = time();
        $token = [
            "jti" => md5($now . rand()),
            "iat" => $now,
            "name" => $customer->getName(),
            "email" => $customer->getEmail()
        ];


        $jwt = $this->jwt->encode($token, $key);
        $location = "https://" . $subdomain . ".zendesk.com/access/jwt?jwt=" . $jwt;

        return $location;
    }

}
