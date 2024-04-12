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
use HealthStatus\HealthStatus;
use HealthStatus\Service\CheckOverridesConfig;
use HealthStatus\Service\ComposerModulesConfig;
use HealthStatus\Service\DatabaseConfig;
use HealthStatus\Service\ExtensionsConfig;
use HealthStatus\Service\HttpsCheckConfig;
use HealthStatus\Service\JwtConfig;
use HealthStatus\Service\ModulesConfig;
use HealthStatus\Service\OrderConfig;
use HealthStatus\Service\PerformanceConfig;
use HealthStatus\Service\ServerConfig;
use HealthStatus\Service\TheliaConfig;
use Exception;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Propel\Generator\Model\Diff\DatabaseDiff;
use Propel\Runtime\Exception\PropelException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response as TheliaResponse ;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Model\AdminQuery;
use OpenApi\Annotations as OA;

/**
 * @Route("/healthstatus/api", name="health_json_server_info")
 */
class HealthJsonController extends BaseFrontController
{
    private string $secretKey;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->secretKey = HealthStatus::getSecretKey();
    }

    #[Route('/server-site-info', name: 'health_server_site_info', methods: ['GET'])]
    /**
     * @OA\Get(
     *     path="/server-site-info",
     *     summary="Get server site information",
     *     tags={"HealthStatus"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     security={
     *         {"BearerAuth": {}}
     *     }
     * )
     * @OA\SecurityScheme(
     *      type="http",
     *      scheme="bearer",
     *      bearerFormat="JWT",
     *      securityScheme="BearerAuth"
     * )
     */
    public function getServerSiteInfo(Request $request)
    {
        if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            return new TheliaResponse('Token not provided', 401);
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
            return new TheliaResponse('Invalid token', 401);
        }

        $ServerConfig = new ServerConfig();
        $theliaConfig = new TheliaConfig();
        $databaseConfig = new DatabaseConfig();
        $performance = new PerformanceConfig();
        $modulesService = new ModulesConfig();
        $httpsCheck = new HttpsCheckConfig();
        $checkOverrideServices = new CheckOverridesConfig();
        $extensions = new ExtensionsConfig();
        $extensions = $extensions->getExtensionsConfig();
        $extensions = $extensions['extensions'];
        $phpConfig = $ServerConfig->getPhpConfig();
        $theliaConfig = $theliaConfig->getTheliaConfig();
        $checkAdminRoute = $ServerConfig->checkRouteAdmin();
        $checkMailNotification = $ServerConfig->checkNotificationsMail();
        $databaseConfig = $databaseConfig->getDatabaseConfig();
        $performance = $performance->getPerformanceConfig();
        $httpsCheck = $httpsCheck->getHttpsCheck();
        $mailCatcherCheck = $modulesService->checkMailCatcherStatus();
        $checkOverrideFiles = $checkOverrideServices->getOverrides();
        $numberOfOverride = $checkOverrideServices->getNumberOfOverrides($checkOverrideFiles);

        $jsonData = json_encode([
            'php' => $phpConfig,
            'thelia' => $theliaConfig,
            'database' => $databaseConfig,
            'performance' => $performance,
            'mailCatcher' => $mailCatcherCheck,
            'checkOverrideFiles' => $checkOverrideFiles,
            'numberOfOverride' => $numberOfOverride,
            'extensions' => $extensions,
            'checkAdminRoute' => $checkAdminRoute,
            'checkMailNotification' => $checkMailNotification,
            'httpsCheck' => $httpsCheck
        ]);

        return new TheliaResponse ($jsonData, 200, ['Content-Type' => 'application/json']);
    }


    #[Route('/modules-info', name: 'health_modules_info', methods: ['GET'])]
    /**
     * @OA\Get(
     *     path="/modules-info",
     *     summary="Get modules information",
     *     tags={"HealthStatus"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     security={
     *         {"BearerAuth": {}}
     *     }
     * )
     */
    public function getModulesInfo(Request $request)
    {
        if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            return new TheliaResponse('Token not provided', 401);
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
            return new TheliaResponse('Invalid token', 401);
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

        $jsonData = json_encode([
            'activeModules' => $activeModules,
            'inactiveModules' => $inactiveModules,
            'numberOfActiveModules' => $numberOfActiveModules,
            'numberOfInactiveModules' => $numberOfInactiveModules,
            'composerModules' => $composerModules
        ]);

        return new TheliaResponse ($jsonData, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/shop-info', name: 'health_shop_info', methods: ['GET'])]
    /**
     * @OA\Get(
     *     path="/shop-info",
     *     summary="Get shop information",
     *     tags={"HealthStatus"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     security={
     *         {"BearerAuth": {}}
     *     }
     * )
     * */
    public function getShopInfo(Request $request)
    {
        if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            header('HTTP/1.0 400 Bad Request');
            return new TheliaResponse('Token not provided', 401);
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
            return new TheliaResponse('Invalid token', 401);
        }

        $order = new OrderConfig();
        $lastOrder = $order->getLastOrder();
        $lastOrderValue = $lastOrder['lastOrderDate'];
        $lastOrderPaid = $order->getLastPaidOrder();
        $lastOrderPaidDate = $lastOrderPaid['lastPaidOrderDate'];
        $lastOrderPaidModule = $lastOrderPaid['lastPaidPaymentModule'];
        $lastProductAdded = $order->getLastProductAdded();
        $lastProductAddedDate = $lastProductAdded['lastProductAddedDate'];

        $jsonData = json_encode([
            'lastOrder' => $lastOrderValue,
            'lastOrderPaid' => $lastOrderPaidDate,
            'lastPaidPaymentModule' => $lastOrderPaidModule,
            'lastProductAdded' => $lastProductAddedDate
        ]);

        return new TheliaResponse ($jsonData, 200, ['Content-Type' => 'application/json']);

    }


    /**
     * @throws Exception
     */
    #[Route('/generate-token', name: 'health_generate_token', methods: ['POST'])]
    /**
     * @OA\Post(
     *     path="/generate-token",
     *     summary="Generate a token",
     *     tags={"HealthStatus"},
     *     @OA\RequestBody(
     *         description="Generate token request body",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function generateToken(Request $request, JwtConfig $jwtConfig): TheliaResponse
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

        return new TheliaResponse($token);
    }
}