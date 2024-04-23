<?php

namespace HealthStatus\Service;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Thelia\Model\ModuleQuery;

class ModulesConfig
{
    /**
     * @throws InvalidArgumentException
     */

    public function getModulesAndSendToEndpoint($url): array
    {
        $modulesList = [];

        try {
            $modules = ModuleQuery::create()->find();

            foreach ($modules as $module) {
                $modulesList[] = [
                    'title' => $module->getTitle(),
                    'status' => $module->getActivate() ? 'active' : 'inactive',
                    'code' => $module->getCode(),
                    'version' => $module->getVersion(),
                ];
            }

            $postData = json_encode(['modules' => $modulesList], JSON_PRETTY_PRINT);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('Erreur cURL : ' . curl_error($ch));
            }

            curl_close($ch);

            return json_decode($result, true) ?? [];
        } catch (Exception $e) {
            echo 'Une erreur s\'est produite : ' . $e->getMessage();
        }

        return $modulesList;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getModules(): array
    {
        $url = 'http://thelia.th/compare.php';
        $remoteModulesList = $this->getModulesAndSendToEndpoint($url);

        if (!empty($remoteModulesList)) {
            return $remoteModulesList;
        }

        $localModulesList = $this->getLocalModules();

        if (!empty($localModulesList)) {
            return $localModulesList;
        }

        return [];
    }


    private function getLocalModules(): array
    {
        $modules = ModuleQuery::create()->find();
        $modulesListLocal = [];

        foreach ($modules as $module) {
            $modulesListLocal[] = [
                'title' => $module->getTitle(),
                'status' => $module->getActivate() ? 'active' : 'inactive',
                'code' => $module->getCode(),
                'version' => $module->getVersion(),
                'latestVersion' => '0',
            ];
        }

        return $modulesListLocal;
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