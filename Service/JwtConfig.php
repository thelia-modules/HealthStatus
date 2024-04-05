<?php

namespace HealthStatus\Service;

use Firebase\JWT\JWT;
use HealthStatus\HealthStatus;
use Thelia\Model\ConfigQuery;

class JwtConfig
{
    private string $secretKey;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->secretKey = HealthStatus::getSecretKey();
    }

    public function generateToken(): string
    {
        $expirationTime = HealthStatus::getExpirationTime();

        if ($expirationTime <= time()) {
            $expirationTime = time() + 900;
            HealthStatus::setConfigValue('expiration_time', $expirationTime);
        }

        $token = array(
            "iss" => ConfigQuery::read('url_site'),
            "exp" => $expirationTime
        );

        $alg = HealthStatus::getAlgorithm();

        return JWT::encode($token, $this->secretKey, $alg);
    }

}
