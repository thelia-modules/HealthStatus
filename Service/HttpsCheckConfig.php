<?php

namespace HealthStatus\Service;

class HttpsCheckConfig
{
    public function getHttpsCheck(): array
    {
        return [
            'value' => $this->isHttps() ? 'yes' : 'no',
            'type' => DefineTypeConfig::determineType('httpsCheck'),
        ];
    }

    private function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
