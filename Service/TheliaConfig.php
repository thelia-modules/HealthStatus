<?php

namespace HealthStatus\Service;

use Thelia\Model\ConfigQuery;

class TheliaConfig
{
    public function getTheliaConfig(): array
    {
        return [
            'theliaVersion' => [
                'label' => 'Thelia Version',
                'value' => ConfigQuery::getTheliaSimpleVersion(),
            ],
            'urlUser' => [
                'label' => 'Site URL',
                'value' => $_SERVER['HTTP_HOST'],
            ],
            'https' => [
                'label' => 'HTTPS Status',
                'value' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'on' : 'off',
            ],
            'langUser' => [
                'label' => 'Language User',
                'value' => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2),
            ],
            'timezone' => [
                'label' => 'Timezone',
                'value' => ini_get('date.timezone'),
            ],
        ];
    }
}
