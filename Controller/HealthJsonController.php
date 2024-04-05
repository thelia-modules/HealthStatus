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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HealthStatus\HealthStatus;
use HealthStatus\Service\CheckOverridesConfig;
use HealthStatus\Service\ComposerModulesConfig;
use HealthStatus\Service\DatabaseConfig;
use HealthStatus\Service\HttpsCheckConfig;
use HealthStatus\Service\JwtConfig;
use HealthStatus\Service\ModulesConfig;
use HealthStatus\Service\PerformanceConfig;
use HealthStatus\Service\PhpConfig;
use HealthStatus\Service\TheliaConfig;
use Exception;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use Symfony\Component\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Model\AdminQuery;
use Thelia\Model\ModuleQuery;

/**
 * @Route("/", name="health_json_server_info")
 */
class HealthJsonController extends BaseFrontOpenApiController
{
    private string $secretKey;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->secretKey = HealthStatus::getSecretKey();
    }

    /**
     * @Route("/info", name="health_info", methods="GET")
     */
    public function getInfoJson(Request $request)
    {
        if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            return new Response('Token not provided', 401);
        }

        $token = $matches[1];
        if (!$token) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, HealthStatus::getAlgorithm()));
        } catch (\Exception $e) {
            header('HTTP/1.0 401 Unauthorized');
            return new Response('Invalid token', 401);
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

        return new Response ($jsonData, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/server_info", name="health_server_info", methods="GET")
     */
    public function getServerInfo(Request $request)
    {

        if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            return new Response('Token not provided', 401);
        }

        $token = $matches[1];
        if (! $token) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, HealthStatus::getAlgorithm()));
        } catch (\Exception $e) {
            header('HTTP/1.0 401 Unauthorized');
            return new Response('Invalid token', 401);
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
     * @Route("/generate-token", name="generate-token", methods={"POST"})
     * @throws Exception
     */
    public function generateToken(Request $request, JwtConfig $jwtConfig): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if (null === $email || null === $password) {
            throw new Exception('Email and password are required');
        }

        $admin = AdminQuery::create()
            ->filterByEmail($email)
            ->findOne();

        if (null === $admin || !$admin->checkPassword($password)) {
            throw new Exception('Invalid credentials');
        }

        $token = $jwtConfig->generateToken();

        return new Response($token);
    }


    /**
     * @Route("/repo-info", name="get-info-repo", methods={"GET"})
     */
    public function fetchGitRepo()
    {
        $accessToken = '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';
        $owner = 'thelia-modules';

        $modules = ModuleQuery::create()->find();

        $modulesCode = [];

        foreach ($modules as $module) {
            $modulesCode[] = $module->getCode();
        }

        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'token ' . $accessToken,
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        $latestTags = [];

        foreach ($modulesCode as $moduleCode) {
            $repo = $moduleCode;

            try {
                $response = $client->get("repos/$owner/$repo/tags");
                $tags = json_decode($response->getBody(), true);

                if (!empty($tags)) {
                    $latestTag = $tags[0];
                    $latestTags[] = [
                        'module' => $moduleCode,
                        'tag' => $latestTag['name'],
                        'commit' => $latestTag['commit']['sha'],
                    ];
                } else {
                    echo "No tags found for module: $moduleCode\n";
                }
            } catch (GuzzleException $e) {
                echo "An error occurred while fetching tags for module: $moduleCode - " . $e->getMessage() . "\n";
            }
        }
        $latestTagsJson = json_encode($latestTags);

        return new Response($latestTagsJson, 200, ['Content-Type' => 'application/json']);
    }


}