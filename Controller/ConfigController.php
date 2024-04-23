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

            if ($expirationTimeSec !== null) {
                HealthStatus::setConfigValue('expiration_time', time() + $expirationTimeSec);
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
    public function regenerateKey()
    {
        HealthStatus::setConfigValue('secret_key', bin2hex(random_bytes(32)));
        return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => 'HealthStatus']);
    }
}
