<?php

namespace HealthStatus\Service;

class DefineTypeConfig
{
    public static function determineType($fieldName): string
    {
        switch ($fieldName) {
            case 'numberOfActiveModules':
            case 'numberOfInactiveModules':
                return 'Performance';
            case 'httpsCheck':
                return 'Security';
            default:
                return 'Unknown';
        }
    }
}
