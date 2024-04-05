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
        $token = array(
            "iss" => ConfigQuery::read('url_site'),
            "exp" => time() + 3600,
        );

        $alg = HealthStatus::getAlgorithm();
        var_dump($alg);
        var_dump($this->secretKey);

        return JWT::encode($token, $this->secretKey, $alg);
    }

}
