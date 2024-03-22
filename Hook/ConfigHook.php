<?php

namespace HealthStatus\Hook;

use ReflectionClass;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;
use Thelia\Model\ModuleQuery;

class ConfigHook extends BaseHook
{
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        #####################################################THELIA CONFIG#####################################################

        $theliaConfig = [
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
                'value' =>
                    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
                        ? 'on'
                        : 'off',
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

        #####################################################PHP CONFIG#####################################################

        $phpConfig = [
            'webServer' => [
                'label' => 'Web Server',
                'value' => $_SERVER['SERVER_SOFTWARE'],
            ],

            'phpVersion' => [
                'label' => 'PHP Version',
                'value' => phpversion(),
            ],

            'memoryLimit' => [
                'label' => 'Memory Limit',
                'value' => ini_get('memory_limit'),
            ],

            'maxPostSize' => [
                'label' => 'Max Post Size',
                'value' => ini_get('post_max_size'),
            ],

            'maxUploadSize' => [
                'label' => 'Max Upload Size',
                'value' => ini_get('upload_max_filesize'),
            ],

            'safeMode' => [
                'label' => 'Safe Mode',
                'value' => ini_get('safe_mode') ? 'on' : 'off',
            ],

            'curlVersion' => [
                'label' => 'Curl Version',
                'value' => function_exists('curl_version')
                    ? curl_version()['version']
                    : 'off',
            ],

            'currentTime' => [
                'label' => 'Current Time',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];

        #####################################################DATABASE CONFIG#####################################################

        $mysqlCon = mysqli_connect(
            'localhost',
            'root',
            'root',
            'thelia-modules'
        );
        $mySQLversion = mysqli_get_server_info($mysqlCon);
        $host = $mysqlCon->query('SELECT @@hostname')->fetch_row()[0];
        $user = $mysqlCon->query('SELECT USER()')->fetch_row()[0];
        $database = $mysqlCon->query('SELECT DATABASE()')->fetch_row()[0];
        $charset = $mysqlCon
            ->query('SELECT @@character_set_database')
            ->fetch_row()[0];
        $collation = $mysqlCon
            ->query('SELECT @@collation_database')
            ->fetch_row()[0];
        $maxAllowedPacket = $mysqlCon
            ->query('SELECT @@max_allowed_packet')
            ->fetch_row()[0];
        $maxConnections = $mysqlCon
            ->query('SELECT @@max_connections')
            ->fetch_row()[0];

        $databaseConfig = [
            'mysql' => [
                'label' => 'Server Version',
                'value' => $mySQLversion,
            ],
            'client_version' => [
                'label' => 'Client Version',
                'value' => $mysqlCon->client_info,
            ],
            'host' => [
                'label' => 'Host',
                'value' => $host,
            ],
            'user' => [
                'label' => 'User',
                'value' => $user,
            ],
            'database' => [
                'label' => 'Database Name',
                'value' => $database,
            ],
            'charset' => [
                'label' => 'Charset',
                'value' => $charset,
            ],
            'collation' => [
                'label' => 'Collation',
                'value' => $collation,
            ],
            'max_allowed_packet' => [
                'label' => 'Max Allowed Packet',
                'value' => $maxAllowedPacket,
            ],
            'max_connections' => [
                'label' => 'Max Connections',
                'value' => $maxConnections,
            ],
        ];

        #####################################################PERF#####################################################

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

        #####################################################TYPES#####################################################

        function determineType($fieldName): string
        {
            switch ($fieldName) {
                case 'numberOfActiveModules':
                case 'numberOfInactiveModules':
                case 'numberOfOverriddenClasses':
                    return 'performance';
                case 'httpsCheck':
                    return 'sécurité';
                default:
                    return 'indéterminé';
            }
        }

        #####################################################MODULES#####################################################

        $modules = ModuleQuery::create()->find();
        $modulesList = [];
        foreach ($modules as $module) {
            $modulesList[] = [
                'code' => $module->getCode(),
                'title' => $module->getTitle(),
                'version' => $module->getVersion(),
                'active' => $module->getActivate(),
            ];
        }

        $activeModules = array_filter($modulesList, function ($module) {
            return $module['code'] !== 'WebProfiler' && $module['active'] == 1;
        });

        $inactiveModules = array_filter($modulesList, function ($module) {
            return $module['active'] == 0;
        });

        $numberOfActiveModules = count($activeModules);

        $numberOfInactiveModules = [
            'value' => count($inactiveModules),
            'type' => determineType('numberOfInactiveModules'),
        ];

        $composerJsonPath =
            '/Users/glaissus/Desktop/thelia-modules/thelia/composer.json';
        $composerModules = [];
        $composerModulesList = json_decode(
            file_get_contents($composerJsonPath),
            true
        )['require'];
        foreach ($composerModulesList as $key => $value) {
            if (strpos($key, 'thelia') !== false) {
                $composerModules[] = [
                    'code' => $key,
                    'version' => $value,
                ];
            }
        }

        $numberOfComposerModules = count($composerModules);

        #####################################################OVERRIDES#####################################################

        //check if all the classes are overridden with reflectionclass

        $classes = get_declared_classes();
        ///get only thelia classes

        $classesThelia = array_filter($classes, function ($class) {
            return strpos($class, 'Thelia') !== false;
        });

        $overriddenClasses = [];

        foreach ($classesThelia as $class) {
            $reflectionClass = new ReflectionClass($class);
            $fullPath = $reflectionClass->getFileName();
            if ($reflectionClass->isUserDefined()) {
                $TheliaPosition = strpos($fullPath, 'thelia/');
                if ($TheliaPosition !== false) {
                    $relativePath = substr($fullPath, $TheliaPosition);
                } else {
                    $relativePath = $fullPath;
                }
                $overriddenClasses[] = [
                    'name' => $class,
                    'path' => $relativePath,
                ];
            }
        }

        $numberOfOverriddenClasses = [
            'value' => count($overriddenClasses),
            'type' => determineType('numberOfOverriddenClasses'),
        ];

        #####################################################HTTPS#####################################################

        $httpsCheck = [
            'value' =>
                !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
                    ? 'yes'
                    : 'no',
            'type' => determineType('httpsCheck'),
        ];

        $event->add(
            $this->render('config/module-config.html', [
                'theliaConfig' => $theliaConfig,
                'phpConfig' => $phpConfig,
                'databaseConfig' => $databaseConfig,
                'activeModules' => $activeModules,
                'inactiveModules' => $inactiveModules,
                'composerModules' => $composerModules,
                'overriddenClasses' => $overriddenClasses,
                'numberOfActiveModules' => $numberOfActiveModules,
                'numberOfInactiveModules' => $numberOfInactiveModules,
                'numberOfComposerModules' => $numberOfComposerModules,
                'numberOfOverriddenClasses' => $numberOfOverriddenClasses,
                'httpsCheck' => $httpsCheck,
                'performance' => $performance,
            ])
        );
    }
}
