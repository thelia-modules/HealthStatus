<?php

namespace HealthStatus\Form;

use HealthStatus\HealthStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Thelia\Form\BaseForm;
class ConfigHealth extends BaseForm
{
    protected function buildForm(): void
    {
        $form = $this->formBuilder;
        $algorithm = HealthStatus::getAlgorithm();

        $form->add('algorithm', ChoiceType::class, [
            'label' => 'Algorithm',
            'choices' => [
                'HS256' => 'HS256',
                'HS384' => 'HS384',
                'HS512' => 'HS512',
            ],
            'required' => true,
            'data' => $algorithm
        ]);


        $expirationTime = HealthStatus::getExpirationTime();
        $expirationTime = $expirationTime - time();
        $expirationTime = $expirationTime / 60;
        $expirationTime = round($expirationTime);

        $expirationTime = max($expirationTime, 0);

        $form->add('expiration_time', TextType::class, [
            'label' => 'Expiration time (in minutes)',
            'required' => true,
            'data' => $expirationTime,
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                    'message' => 'The expiration time must be greater than 0.',
                ]),
            ],
        ]);
    }

    public static function getName()
    {
        return 'healthstatus_configuration';
    }
}