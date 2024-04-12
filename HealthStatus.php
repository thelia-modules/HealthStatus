<?php

namespace HealthStatus;

use Propel\Runtime\Connection\ConnectionInterface;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class HealthStatus extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'healthstatus';

    const MODULE_SUBJECT = 'HealthStatus';

    public static function getComposerVersion(): string
    {
        return explode(' ', shell_exec('composer --version'))[2];
    }

    public static function getNodeVersion(): string
    {
        return shell_exec('node -v');
    }


    /**
     * @throws RandomException
     */
    public static function getSecretKey(): ?string
    {
        if (self::getConfigValue('secret_key') === null) {
            self::setConfigValue('secret_key', bin2hex(random_bytes(32)));
        } else {
            return self::getConfigValue('secret_key');
        }
        return self::getConfigValue('secret_key');
    }

    public static function getAlgorithm(): ?string
    {
        if (self::getConfigValue('algorithm') === null) {
            self::setConfigValue('algorithm', 'HS256');
        } else {
            return self::getConfigValue('algorithm');
        }
        return self::getConfigValue('algorithm');
    }


    public static function getExpirationTime(): ?int
    {
        if (self::getConfigValue('expiration_time') === null) {
            self::setConfigValue('expiration_time', time() + 900);
        } else {
            return self::getConfigValue('expiration_time');
        }
        return self::getConfigValue('expiration_time');
    }


    /*
     * You may now override BaseModuleInterface methods, such as:
     * install, destroy, preActivation, postActivation, preDeactivation, postDeactivation
     *
     * Have fun !
     */

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }

    /**
     * Execute sql files in Config/update/ folder named with module version (ex: 1.0.1.sql).
     *
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $updateDir = __DIR__.DS.'Config'.DS.'update';

        if (! is_dir($updateDir)) {
            return;
        }

        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in($updateDir);

        $database = new Database($con);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }
    }
}
