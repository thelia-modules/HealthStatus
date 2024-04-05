<?php

namespace HealthStatus\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ModuleConfig;
use Thelia\Model\ModuleConfigQuery;

class ConfigurationKey extends BaseForm
{
    protected function buildForm(): void
    {
        $form = $this->formBuilder;
        $algorithm = ModuleConfigQuery::create()->findOneByName('algorithm');
        if (null === $algorithm) {
            $algorithm = new ModuleConfig();
            $algorithm->setName('algorithm');
            $algorithm->setValue('HS256');
        } else {
            $algorithm = $algorithm->getValue();
        }

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
    }

    public static function getName()
    {
        return 'healthstatus_configuration';
    }
}