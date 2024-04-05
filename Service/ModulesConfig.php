<?php

namespace HealthStatus\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Thelia\Model\ModuleQuery;

class ModulesConfig
{
    public function getModules(): array
    {
        $accessToken = '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';
        $owner = 'thelia-modules';

        $modules = ModuleQuery::create()->find();
        $modulesList = [];

        $cache = new FilesystemAdapter();

        foreach ($modules as $module) {
            $moduleCode = $module->getCode();
            $repo = $moduleCode;
            $cacheItem = $cache->getItem("module_$repo");
            if ($cacheItem->isHit()) {
                $latestVersion = $cacheItem->get();
            } else {
                $latestVersion = '0';
                try {
                    $client = new Client([
                        'base_uri' => 'https://api.github.com/',
                        'headers' => [
                            'Authorization' => 'token ' . $accessToken,
                            'Accept' => 'application/vnd.github.v3+json',
                        ],
                    ]);
                    $response = $client->get("repos/$owner/$repo/tags");
                    $tags = json_decode($response->getBody(), true);

                    if (!empty($tags)) {
                        $latestTag = $tags[0];
                        $latestVersion = $latestTag['name'];
                    }

                    $cacheItem->set($latestVersion);
                    $cache->save($cacheItem);
                } catch (GuzzleException $e) {
                }
            }

            $modulesList[] = [
                'code' => $module->getCode(),
                'title' => $module->getTitle(),
                'active' => $module->getActivate(),
                'version' => $module->getVersion(),
                'latestVersion' => $latestVersion,
            ];
        }

        return $modulesList;
    }


    public function getActiveModules(array $modulesList): array
    {
        return array_filter($modulesList, function ($module) {
            return $module['code'] !== 'WebProfiler' && $module['active'] == 1;
        });
    }

    public function getInactiveModules(array $modulesList): array
    {
        return array_filter($modulesList, function ($module) {
            return $module['active'] == 0;
        });
    }

    public function getNumberOfActiveModules(array $activeModules): int
    {
        return count($activeModules);
    }

    public function getNumberOfInactiveModules(array $inactiveModules): array
    {
        return [
            'value' => count($inactiveModules),
            'type' => DefineTypeConfig::determineType('numberOfInactiveModules'),
        ];
    }
}
