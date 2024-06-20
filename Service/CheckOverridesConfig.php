<?php

namespace HealthStatus\Service;

class CheckOverridesConfig
{
    public function getOverrides(): array
    {
        $overrides = [];
        $composerJsonPath = THELIA_ROOT.'/composer.json';

        try {
            if (file_exists($composerJsonPath)) {
                $composerJsonContent = file_get_contents($composerJsonPath);
                if ($composerJsonContent === false) {
                    throw new \Exception("Unable to read composer.json");
                }

                $composerJson = json_decode($composerJsonContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Error decoding composer.json: " . json_last_error_msg());
                }

                $autoload = $composerJson['autoload'];
                if (!isset($autoload['psr-4'])) {
                    throw new \Exception("psr-4 not found in composer.json");
                }

                $psr4 = $autoload['psr-4'];
                $this->processPaths($psr4, $overrides);
            }
        } catch (\Exception $e) {
            // Log the error message or handle it as needed
            error_log($e->getMessage());
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
                try {
                    $this->scanOverrideDirectory($overridePath, $overrides);
                } catch (\Exception $e) {
                    // Log the error message or handle it as needed
                    error_log($e->getMessage());
                }
            }
        }
    }

    private function scanOverrideDirectory($directory, &$overrides)
    {
        try {
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getNumberOfOverrides(array $overrides): int
    {
        return count($overrides);
    }
}
