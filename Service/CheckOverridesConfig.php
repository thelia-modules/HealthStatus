<?php

namespace HealthStatus\Service;

class CheckOverridesConfig
{
    public function getOverrides(): array
    {
        $overrides = [];
        $composerJsonPath = THELIA_ROOT.'/composer.json';

        if (file_exists($composerJsonPath)) {
            $composerJson = json_decode(file_get_contents($composerJsonPath), true);
            $autoload = $composerJson['autoload'];
            $psr4 = $autoload['psr-4'];
            $this->processPaths($psr4, $overrides);
        }
        return $overrides;
    }
    private function processPaths($paths, &$overrides)
    {
        foreach ($paths as $path) {
            if (is_array($path)) {
                $this->processPaths($path, $overrides);
            } else {
                $this->addOverrideIfPathStartsWithOverride($path, $overrides);
            }
        }
    }
    private function addOverrideIfPathStartsWithOverride($path, &$overrides)
    {
        if (str_starts_with($path, 'override/')) {
            $overridePath = THELIA_ROOT.'/'.$path;
            if (file_exists($overridePath)) {
                $overridesList = scandir($overridePath);
                $path = str_replace(THELIA_ROOT, '', $path);
                foreach ($overridesList as $value) {
                    if ($value !== '.' && $value !== '..') {
                        $fullPath = $overridePath.'/'.$value;
                        if (!is_dir($fullPath)) {
                            $overrides[] = [
                                'path' => $path,
                                'file' => $value
                            ];
                        }
                    }
                }
            }
        }
    }

    public function getNumberOfOverrides(array $overrides): int
    {
        return count($overrides);
    }
}
