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

namespace HealthStatus\Controller;

use HealthStatus\Model\HealthKeys;
use HealthStatus\Model\HealthKeysQuery;
use HealthStatus\Service\CheckOverridesConfig;
use HealthStatus\Service\ComposerModulesConfig;
use HealthStatus\Service\DatabaseConfig;
use HealthStatus\Service\HttpsCheckConfig;
use HealthStatus\Service\ModulesConfig;
use HealthStatus\Service\PerformanceConfig;
use HealthStatus\Service\PhpConfig;
use HealthStatus\Service\TheliaConfig;
use Propel\Runtime\Exception\PropelException;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Model\Admin;

/**
 * @Route("/admin/healthstatus")
 */
class HealthJsonController extends BaseFrontController
{
    /**
     * @Route("/info", name="status_json_info", methods="GET")
     */
    public function getInfoJson(Request $request)
    {

        $healthKey = HealthKeysQuery::create()->findOne();
        if ($healthKey === null) {
            return new Response('Accès refusé. Clé secrète invalide.', 403);
        } else
            $healthKey = $healthKey->getSecretKey();

        if ($healthKey === null ) {
            return new Response('Accès refusé. Clé secrète invalide.', 403);
        }


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

        $jsonData = json_encode([
            'activeModules' => $activeModules,
            'inactiveModules' => $inactiveModules,
            'composerModules' => $composerModules,
            'numberOfActiveModules' => $numberOfActiveModules,
            'numberOfInactiveModules' => $numberOfInactiveModules,
            'numberOfComposerModules' => $numberOfComposerModules,
            'httpsCheck' => $httpsCheck,
            'overrideFiles' => $checkOverrideFiles,
            'numberOfOverride' => $numberOfOverride,
        ]);

        return new Response($jsonData, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/server_info", name="health_json_server_info", methods="GET")
     */
    public function getServerInfo(Request $request)
    {

        $healthKey = HealthKeysQuery::create()->findOne();
        if ($healthKey === null) {
            return new Response('Accès refusé. Clé secrète invalide.', 403);
        } else
        $healthKey = $healthKey->getSecretKey();

        if ($healthKey === null ) {
            return new Response('Accès refusé. Clé secrète invalide.', 403);
        }

        $phpConfigService = new PhpConfig();
        $phpConfig = $phpConfigService->getPhpConfig();

        $theliaConfig = new TheliaConfig();
        $theliaConfig = $theliaConfig->getTheliaConfig();

        $databaseConfig = new DatabaseConfig();
        $databaseConfig = $databaseConfig->getDatabaseConfig();

        $performance = new PerformanceConfig();
        $performance = $performance->getPerformanceConfig();

        $jsonData = json_encode([
            'theliaConfig' => $theliaConfig,
            'phpConfig' => $phpConfig,
            'databaseConfig' => $databaseConfig,
            'performance' => $performance,
        ]);

        return new Response($jsonData, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/generate-key", name="generate_key", methods="GET")
     * @throws PropelException
     * @throws RandomException
     */
    public function generateKey(Request $request)
    {

       $adminId = $request->request->get('adminID');
        if (null === $adminId) {
            return new Response('Admin ID is required', 400, ['Content-Type' => 'application/json']);
        }

        $secretKey = bin2hex(random_bytes(32));

        $existingHealthKey = HealthKeysQuery::create()->findOneByAdminId($adminId);

        if ($existingHealthKey) {
            $existingHealthKey->setSecretKey($secretKey);
            $existingHealthKey->setUpdatedAt(new \DateTime());
            $existingHealthKey->save();
        } else {
            $healthKey = new HealthKeys();
            $healthKey->setAdminId($adminId);
            $healthKey->setSecretKey($secretKey);
            $healthKey->setCreatedAt(new \DateTime());
            $healthKey->setUpdatedAt(new \DateTime());
            $healthKey->save();
        }

        return new Response(json_encode(['secretKey' => $secretKey]), 200, ['Content-Type' => 'application/json']);
    }


}