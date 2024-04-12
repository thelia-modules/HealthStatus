<?php

namespace HealthStatus\Service;

class PerformanceConfig
{
    public function getPerformanceConfig(): array
    {
        $memoryUsage = memory_get_usage(true);
        $averageMemoryUsage = memory_get_peak_usage(true);

        return [
            'memoryUsage' => [
                'label' => 'Memory usage',
                'value' => $memoryUsage,
            ],
            'averageMemoryUsage' => [
                'label' => 'Average memory usage over the last week',
                'value' => $averageMemoryUsage,
            ],
        ];
    }


}
