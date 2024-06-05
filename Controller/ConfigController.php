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

use HealthStatus\Form\ConfigHealth;
use HealthStatus\HealthStatus;
use Random\RandomException;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/module/healthstatus", name="health_config")
 */
class ConfigController extends BaseAdminController
{
    public function setAction()
    {
        $form = $this->createForm(ConfigHealth::getName());

        try {
            $healthForm = $this->validateForm($form);
            $formData = $healthForm->all();
            $algorithm = $formData['algorithm'];
            $expirationTimeForm = $formData['expiration_time'];
            $expirationTimeMin = $expirationTimeForm->getData();
            $expirationTimeSec = $expirationTimeMin * 60;
            $algorithm = $algorithm->getData();
            $urlShare = $formData['url_share'];
            $urlShare = $urlShare->getData();

            if ($expirationTimeSec !== null) {
                HealthStatus::setConfigValue('expiration_time', time() + $expirationTimeSec);
            }

            if ($urlShare !== null) {
                HealthStatus::setConfigValue('share_url', $urlShare);
            }

            if ($algorithm !== null) {
                HealthStatus::setConfigValue('algorithm', $algorithm);
            }

            $response = $this->redirectAction();

        } catch (FormValidationException $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans('Error', []),
                $e->getMessage(),
                $form
            );

            return $this->generateSuccessRedirect($form);
        }

        return $response;

    }

    public function redirectAction()
    {
        return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => 'HealthStatus']);
    }

    /**
     * @throws RandomException
     */
    /**
     * @Route("/regenerate", name="health_regenerate_key")
     */
    public function regenerateKey()
    {
        HealthStatus::setConfigValue('secret_key', bin2hex(random_bytes(32)));
        return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => 'HealthStatus']);
    }

    /**
     * @Route("/authorize", name="health_authorize_url")
     */
    public function authorizeUrl()
    {
        HealthStatus::setConfigValue('share_url', 1);

       return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => 'HealthStatus']);
    }


    /**
     * @Route("/not-authorize", name="health_not_authorize_url")
     */
    public function notAuthorizeUrl()
    {
        HealthStatus::setConfigValue('share_url', 0);

        return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => 'HealthStatus']);
    }

}
