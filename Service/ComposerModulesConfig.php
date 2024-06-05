<?php

namespace HealthStatus\Service;

class ComposerModulesConfig
{
    public function getComposerModules(string $composerJsonPath): array
    {
        $composerModules = [];
        $composerModulesList = json_decode(
            file_get_contents($composerJsonPath),
            true
        )['require'];
        foreach ($composerModulesList as $key => $value) {
            if (strpos($key, 'thelia') !== false) {
                $version = preg_replace('/[^0-9.]/', '', $value);
                $composerModules[] = [
                    'code' => $key,
                    'version' => $version,
                ];
            }
        }
        return $composerModules;
    }

    public function getNumberOfComposerModules(array $composerModules): int
    {
        return count($composerModules);
    }
}

