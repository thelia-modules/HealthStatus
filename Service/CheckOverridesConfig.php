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
        foreach ($paths as $namespace => $path) {
            if (is_array($path)) {
                foreach ($path as $subPath) {
                    $this->addOverridesFromPath($subPath, $overrides);
                }
            } else {
                $this->addOverridesFromPath($path, $overrides);
            }
        }
    }

    private function addOverridesFromPath($path, &$overrides)
    {
        if (strpos($path, 'override/') === 0) {
            $overridePath = THELIA_ROOT.'/'.$path;
            if (file_exists($overridePath)) {
                $this->scanOverrideDirectory($overridePath, $overrides);
            }
        }
    }

    private function scanOverrideDirectory($directory, &$overrides)
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace(THELIA_ROOT, '', $file->getPathname());
                $overrides[] = [
                    'path' => dirname($relativePath),
                    'file' => basename($relativePath)
                ];
            }
        }
    }

    public function getNumberOfOverrides(array $overrides): int
    {
        return count($overrides);
    }
}
