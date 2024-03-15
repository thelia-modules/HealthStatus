<?php

namespace HealthStatus\Hook;

use HealthStatus\HealthStatus;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class HookManager extends BaseHook
{
    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'health_status_menu_tags',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/health'),
                'title' => $this->trans('Health Status', [], HealthStatus::DOMAIN_NAME)
            ]
        );
    }

}
