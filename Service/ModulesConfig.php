<?php

namespace HealthStatus\Service;

use HealthStatus\HealthStatus;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Thelia\Model\ModuleQuery;

class ModulesConfig
{
    /**
     * @throws InvalidArgumentException
     */
    public function getModules(): array
    {
        $accessToken = HealthStatus::getGitHubToken();
        $owner = 'thelia-modules';

        $modules = ModuleQuery::create()->find();
        $modulesList = [];

        $cache = new FilesystemAdapter();

        $client = HttpClient::create([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'token ' . $accessToken,
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        foreach ($modules as $module) {
            $moduleCode = $module->getCode();
            $repo = $moduleCode;
            $cacheItem = $cache->getItem("module_$repo");
            if ($cacheItem->isHit()) {
                $latestVersion = $cacheItem->get();
            } else {
                $latestVersion = '0';
                try {
                    $response = $client->request('GET', "repos/$owner/$repo/tags");
                    $tags = $response->toArray();

                    if (!empty($tags)) {
                        $latestTag = $tags[0];
                        $latestVersion = $latestTag['name'];
                    }

                    $cacheItem->set($latestVersion);
                    $cache->save($cacheItem);
                } catch (\Exception $e) {
                }
            }

            $modulesList[] = [
                'code' => $module->getCode(),
                'title' => $module->getTitle(),
                'status' => $module->getActivate() == 1 ? 'active' : 'inactive',
                'version' => $module->getVersion(),
                'latestVersion' => $latestVersion,
            ];
        }
        return $modulesList;
    }


    public function getActiveModules(array $modulesList): array
    {
        return array_filter($modulesList, function ($module) {
            return $module['code'] !== 'WebProfiler' && $module['status'] == 'active';
        });
    }

    public function getInactiveModules(array $modulesList): array
    {
        return array_filter($modulesList, function ($module) {
            return $module['status'] == 'inactive';
        });
    }

    public function getNumberOfActiveModules(array $activeModules): int
    {
        return count($activeModules);
    }

    public function getNumberOfInactiveModules(array $inactiveModules): int
    {
        return count($inactiveModules);
    }

    public function checkMailCatcherStatus(): bool
    {

        $mailCatcherModule = ModuleQuery::create()
            ->filterByCode('TheliaMailCatcher')
            ->findOne();

        if ($mailCatcherModule == null || $mailCatcherModule->getActivate() == 0) {
            return false;
        }
        return true;
    }


}
