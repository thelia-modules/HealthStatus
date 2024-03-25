<?php

namespace HealthStatus\Service;

class PhpConfig
{
    public function getPhpConfig(): array
    {
        return [
            'webServer' => [
                'label' => 'Web Server',
                'value' => $_SERVER['SERVER_SOFTWARE'],
            ],

            'phpVersion' => [
                'label' => 'PHP Version',
                'value' => phpversion(),
            ],

            'memoryLimit' => [
                'label' => 'Memory Limit',
                'value' => ini_get('memory_limit'),
            ],

            'maxPostSize' => [
                'label' => 'Max Post Size',
                'value' => ini_get('post_max_size'),
            ],

            'maxUploadSize' => [
                'label' => 'Max Upload Size',
                'value' => ini_get('upload_max_filesize'),
            ],

            'safeMode' => [
                'label' => 'Safe Mode',
                'value' => ini_get('safe_mode') ? 'on' : 'off',
            ],

            'curlVersion' => [
                'label' => 'Curl Version',
                'value' => function_exists('curl_version') ? curl_version()['version'] : 'off',
            ],

            'currentTime' => [
                'label' => 'Current Time',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}
