<?php

namespace HealthStatus\Service;

use Thelia\Model\ModuleQuery;

class ModulesConfig
{
    public function getModules(): array
    {
        $modules = ModuleQuery::create()->find();
        $modulesList = [];
        foreach ($modules as $module) {
            $modulesList[] = [
                'code' => $module->getCode(),
                'title' => $module->getTitle(),
                'version' => $module->getVersion(),
                'active' => $module->getActivate(),
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
