<?php

namespace HealthStatus\Service;

class PerformanceConfig
{
    public function getPerformanceConfig(): array
    {
        $performance = [
            'memoryUsage' => [
                'label' => 'Memory usage',
                'value' => memory_get_usage(),
            ],
            'peakMemoryUsage' => [
                'label' => 'Peak memory usage',
                'value' => memory_get_peak_usage(),
            ],
        ];

        return $performance;

    }
}
