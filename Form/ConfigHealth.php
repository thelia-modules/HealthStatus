<?php

namespace HealthStatus\Form;

use HealthStatus\HealthStatus;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Thelia\Form\BaseForm;
use function Symfony\Component\Translation\t;

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
            'required' => false,
            'data' => $algorithm
        ]);


        $expirationTime = HealthStatus::getExpirationTime();
        $expirationTime = $expirationTime - time();
        $expirationTime = $expirationTime / 60;
        $expirationTime = round($expirationTime);

        $expirationTime = max($expirationTime, 0);

        $form->add('expiration_time', TextType::class, [
            'label' => 'Expiration time (in minutes)',
            'required' => false,
            'data' => $expirationTime,
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                    'message' => 'The expiration time must be greater than 0.',
                ]),
            ],
        ]);

        $urlShare = HealthStatus::getUrlShare();

        $form->add('url_share', ChoiceType::class, [
            'label' => 'URL Share',
            'choices' => [
                'Yes' => 1,
                'No' => 0,
            ],
            'required' => false,
            'data' => $urlShare
        ]);
    }

    public static function getName()
    {
        return 'healthstatus_configuration';
    }
}