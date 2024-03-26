<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HealthStatus\Hook;

use HealthStatus\Service\CheckOverridesConfig;
use HealthStatus\Service\ComposerModulesConfig;
use HealthStatus\Service\DatabaseConfig;
use HealthStatus\Service\HttpsCheckConfig;
use HealthStatus\Service\ModulesConfig;
use HealthStatus\Service\PerformanceConfig;
use HealthStatus\Service\PhpConfig;
use HealthStatus\Service\TheliaConfig;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ConfigHook extends BaseHook
{
    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $phpConfigService = new PhpConfig();
        $phpConfig = $phpConfigService->getPhpConfig();

        $theliaConfig = new TheliaConfig();
        $theliaConfig = $theliaConfig->getTheliaConfig();

        $databaseConfig = new DatabaseConfig();
        $databaseConfig = $databaseConfig->getDatabaseConfig();

        $performance = new PerformanceConfig();
        $performance = $performance->getPerformanceConfig();

        $modulesService = new ModulesConfig();
        $modulesList = $modulesService->getModules();
        $activeModules = $modulesService->getActiveModules($modulesList);
        $inactiveModules = $modulesService->getInactiveModules($modulesList);
        $numberOfActiveModules = $modulesService->getNumberOfActiveModules($activeModules);
        $numberOfInactiveModules = $modulesService->getNumberOfInactiveModules($inactiveModules);

        $composerJsonPath = THELIA_ROOT.'/composer.json';
        $composerModulesService = new ComposerModulesConfig();
        $composerModules = $composerModulesService->getComposerModules($composerJsonPath);

        $numberOfComposerModules = $composerModulesService->getNumberOfComposerModules($composerModules);

        $httpsCheck = new HttpsCheckConfig();
        $httpsCheck = $httpsCheck->getHttpsCheck();

        $checkOverrideServices = new CheckOverridesConfig();
        $checkOverrideFiles = $checkOverrideServices->getOverrides();
        $numberOfOverride = $checkOverrideServices->getNumberOfOverrides($checkOverrideFiles);

        $event->add(
            $this->render('config/module-config.html', [
                'theliaConfig' => $theliaConfig,
                'phpConfig' => $phpConfig,
                'databaseConfig' => $databaseConfig,
                'activeModules' => $activeModules,
                'inactiveModules' => $inactiveModules,
                'composerModules' => $composerModules,
                'numberOfActiveModules' => $numberOfActiveModules,
                'numberOfInactiveModules' => $numberOfInactiveModules,
                'numberOfComposerModules' => $numberOfComposerModules,
                'httpsCheck' => $httpsCheck,
                'performance' => $performance,
                'overrideFiles' => $checkOverrideFiles,
                'numberOfOverride' => $numberOfOverride,
            ])
        );
    }
}
