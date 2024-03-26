<?php

namespace HealthStatus\Service;

use Thelia\Model\ModuleQuery;

class ModulesConfig
{
    public function getModules(): array
    {
        // Initialisation des modules dans un tableau $modules.
        $modules = ModuleQuery::create()->find();
        $modulesList = [];

        $multiCurl = curl_multi_init();
        $curlHandles = [];

        // Formatage des noms des modules pour les requêtes curl
        foreach ($modules as $module) {
            $moduleCode = $module->getCode();
            $moduleCode = preg_replace('/([a-z])([A-Z])/', '$1-$2', $moduleCode);
            $moduleCode = strtolower($moduleCode);
            $moduleFormat = $moduleCode . '-module.json';

            $url = 'https://repo.packagist.org/p2/thelia/' . $moduleFormat;

            // Initialisation de la requête curl
            $curlHandle = curl_init($url);
            // Configuration de la requête curl
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);

            // Ajout de la requête curl à la requête multiple
            curl_multi_add_handle($multiCurl, $curlHandle);
            $curlHandles[] = $curlHandle;
        }

        // Exécution des requêtes curl
        $active = null;
        do {
            $status = curl_multi_exec($multiCurl, $active);
        } while ($status == CURLM_CALL_MULTI_PERFORM || $active);

        // Récupération des résultats des requêtes curl
        foreach ($curlHandles as $index => $curlHandle) {
            $jsonContent = curl_multi_getcontent($curlHandle);
            curl_multi_remove_handle($multiCurl, $curlHandle);
            curl_close($curlHandle);

            $latestVersion = '0'; // Message par défaut
            if ($jsonContent !== false) {
                $jsonArray = json_decode($jsonContent, true);
                if (isset($jsonArray['packages']) && is_array($jsonArray['packages'])) {
                    foreach ($jsonArray['packages'] as $moduleName => $moduleDetails) {
                        $latestVersion = $moduleDetails[0]['version'];
                        break;
                    }
                }
            }

            // Ajout des informations des modules dans le tableau $modulesList
            $modulesList[] = [
                'code' => $modules[$index]->getCode(),
                'title' => $modules[$index]->getTitle(),
                'active' => $modules[$index]->getActivate(),
                'version' => $modules[$index]->getVersion(),
                'latestVersion' => $latestVersion,
            ];
        }

        // Fermeture de la requête multiple
        curl_multi_close($multiCurl);

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
