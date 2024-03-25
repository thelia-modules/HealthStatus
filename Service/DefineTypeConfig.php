<?php

namespace HealthStatus\Service;

class DefineTypeConfig
{
    public static function determineType($fieldName): string
    {
        switch ($fieldName) {
            case 'numberOfActiveModules':
            case 'numberOfInactiveModules':
                return 'performance';
            case 'httpsCheck':
                return 'sécurité';
            default:
                return 'indéterminé';
        }
    }
}
