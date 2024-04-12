<?php

namespace HealthStatus\Service;

class ExtensionsConfig
{
    public function getExtensionsConfig(): array
    {
        $extensions = get_loaded_extensions();
        $requiredExtensions = [
            'pdo_mysql',
            'openssl',
            'curl',
            'gd',
            'dom',
            'intl',
            'test',
            'test2',
        ];
        $missingExtensions = array_diff($requiredExtensions, $extensions);
        $hasMissingExtensions = !empty($missingExtensions);

        return [
            'extensions' => [
                'label' => 'Extensions',
                'value' => $extensions,
                'missing' => $missingExtensions,
                'hasMissingExtensions' => $hasMissingExtensions,
            ],
        ];
    }
}
