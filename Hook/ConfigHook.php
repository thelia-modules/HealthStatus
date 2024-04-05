<?php

namespace HealthStatus\Hook;


use HealthStatus\HealthStatus;
use Random\RandomException;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ConfigHook extends BaseHook
{
    /**
     * @throws RandomException
     */
    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $expirationTime = HealthStatus::getExpirationTime();
        $expirationTime = $expirationTime - time();
        $secretKey = HealthStatus::getSecretKey();


        if ($expirationTime <= 0) {
            HealthStatus::setConfigValue('expiration_time', 0);
        } else {
            $expirationTime = $expirationTime / 60;
            $expirationTime = round($expirationTime);
        }

        $event->add($this->render("config/module-config.html", [
            'expiration_time' => $expirationTime,
            'secret_key' => $secretKey
        ]));
    }
}