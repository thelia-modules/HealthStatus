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

use HealthStatus\Form\ConfigurationKey;
use HealthStatus\HealthStatus;
use HealthStatus\Service\JwtConfig;
use Random\RandomException;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;

class ConfigController extends BaseAdminController
{
    public function setAction()
    {
        $form = $this->createForm(ConfigurationKey::getName());

        try {
            $healthForm = $this->validateForm($form);
            $formData = $healthForm->all();
            $algorithm = $formData['algorithm'];

            $algorithm = $algorithm->getData();


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
