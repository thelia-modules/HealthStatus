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

use HealthStatus\HealthStatus;
use HealthStatus\Service\CheckOverridesConfig;
use HealthStatus\Service\ComposerModulesConfig;
use HealthStatus\Service\DatabaseConfig;
use HealthStatus\Service\ExtensionsConfig;
use HealthStatus\Service\HttpsCheckConfig;
use HealthStatus\Service\ModulesConfig;
use HealthStatus\Service\OrderConfig;
use HealthStatus\Service\PerformanceConfig;
use HealthStatus\Service\ServerConfig;
use HealthStatus\Service\TheliaConfig;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/healthstatus", name="health")
 */
class HealthController extends BaseAdminController
{
    protected mixed $subject;

    public function __construct()
    {
        $this->subject = HealthStatus::MODULE_SUBJECT;
    }

    /**
     * @Route("/show", name="_show_info", methods="GET")
     * @throws PropelException
     */
    public function index(EventDispatcherInterface $eventDispatcher)
    {
        $subject = $this->subject;
        $event = new GenericEvent($subject);
        $eventDispatcher->dispatch($event, 'module.config');
        $modulesConfigCheck = $event->getArguments();

        $ServerConfig = new ServerConfig();
        $phpConfig = $ServerConfig->getPhpConfig();
        $checkAdminRoute = $ServerConfig->checkRouteAdmin();
        $checkMailNotification = $ServerConfig->checkNotificationsMail();
        $checkEditRobotsFile = $ServerConfig->checkRobotsTxtFile();

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
        $mailCatcherCheck = $modulesService->checkMailCatcherStatus();

        $composerJsonPath = THELIA_ROOT . '/composer.json';
        $composerModulesService = new ComposerModulesConfig();
        $composerModules = $composerModulesService->getComposerModules($composerJsonPath);

        $numberOfComposerModules = $composerModulesService->getNumberOfComposerModules($composerModules);

        $httpsCheck = new HttpsCheckConfig();
        $httpsCheck = $httpsCheck->getHttpsCheck();

        $checkOverrideServices = new CheckOverridesConfig();
        $checkOverrideFiles = $checkOverrideServices->getOverrides();
        $numberOfOverride = $checkOverrideServices->getNumberOfOverrides($checkOverrideFiles);

        $extensions = new ExtensionsConfig();
        $extensions = $extensions->getExtensionsConfig();
        $extensions = $extensions['extensions'];

        $order = new OrderConfig();
        $lastOrder = $order->getLastOrder();
        $lastOrderValue = $lastOrder['lastOrderDate'];
        $lastOrderPaid = $order->getLastPaidOrder();
        $lastOrderPaidDate = $lastOrderPaid['lastPaidOrderDate'];
        $lastOrderPaidModule = $lastOrderPaid['lastPaidPaymentModule'];
        $lastProductAdded = $order->getLastProductAdded();
        $lastProductAddedDate = $lastProductAdded['lastProductAddedDate'];

        return
            $this->render('health', [
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
                    'extensions' => $extensions,
                    'lastOrder' => $lastOrderValue,
                    'lastOrderPaid' => $lastOrderPaidDate,
                    'lastPaidPaymentModule' => $lastOrderPaidModule,
                    'lastProductAdded' => $lastProductAddedDate,
                    'mailCatcherCheck' => $mailCatcherCheck,
                    'checkAdminRoute' => $checkAdminRoute,
                    'moduleConfigCheck' => $modulesConfigCheck,
                    'checkMailNotification' => $checkMailNotification,
                    'checkEditRobotsFile' => $checkEditRobotsFile
                ]
            );
    }
}
