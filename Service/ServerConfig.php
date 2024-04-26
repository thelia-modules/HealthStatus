<?php

namespace HealthStatus\Service;

use EditRobotTxt\Model\RobotsQuery;
use HealthStatus\HealthStatus;
use Thelia\Model\ConfigQuery;

class ServerConfig
{
    public function getPhpConfig(): array
    {
        return [
            'webServer' => [
                'label' => 'Web Server',
                'value' => $_SERVER['SERVER_SOFTWARE'],
            ],
            'phpVersion' => [
                'label' => 'PHP Server',
                'value' => phpversion(),
            ],
            'phpCli' => [
                'label' => 'PHP CLI',
                'value' => phpversion(),
            ],
            'composerVersion' => [
                'label' => 'Composer Version',
                'value' => HealthStatus::getComposerVersion(),
            ],
            'nodeVersion' => [
                'label' => 'Node Version',
                'value' => HealthStatus::getNodeVersion(),
            ],
            'memoryLimit' => [
                'label' => 'Memory Limit',
                'valueConvert' => $this->memoryToBytes(ini_get('memory_limit')),
                'value' => ini_get('memory_limit'),
                'recommended' => 256 * 1024,
                'type' => 'Performance',
            ],
            'maxPostSize' => [
                'label' => 'Max Post Size',
                'valueConvert' => $this->memoryToBytes(ini_get('post_max_size')),
                'value' => ini_get('post_max_size'),
                'recommended' => 20 * 1024,
                'type' => 'Performance',
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

    function memoryToBytes($value): int
    {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int)$value;
        switch ($unit) {
            case 'm':
                $value *= 1024;
        }
        return $value;
    }

    public function checkRouteAdmin()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/admin';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($status === 302 || $status === 200) {
            return 'enabled';
        } else {
            return 'disabled';
        }
    }

    public function checkNotificationsMail()
    {
        $configMail = ConfigQuery::create()
            ->filterByName('store_notification_emails')
            ->findOne();

        if ($configMail !== null) {
            return 'enabled';
        } else {
            return 'disabled';
        }

    }

    public function checkRobotsTxtFile(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/robots.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $configRobots = RobotsQuery::create()
            ->findOneByDomainName($_SERVER['HTTP_HOST']);

        $fileContent = $configRobots->getRobotsContent();

        if ($status === 200 && $fileContent !== null &&
            str_contains($fileContent, '@url: ' . $protocol . "://" . $_SERVER['HTTP_HOST']) &&
            str_contains($fileContent, 'Sitemap: ' . $protocol . "://" . $_SERVER['HTTP_HOST'] . '/sitemap')) {
            return 'enabled';
        } else {
            return 'disabled';
        }
    }

}
