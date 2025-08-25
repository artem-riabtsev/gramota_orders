<?php

namespace App\Form;

use Brick\Money\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppMoneyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            // Transform: Model → Normalized (форма)
            function ($money) {
                return (string)$money->getAmount();
            },

            // ReverseTransform: Normalized (форма) → Model (после submit)
            function ($floatValue) {

                if ($floatValue === null || $floatValue === '' || !is_numeric($floatValue)) {
                    throw new TransformationFailedException();
                }

                try {
                    return Money::of($floatValue, 'RUB');
                } catch (\Exception $e) {
                    throw new TransformationFailedException();
                }
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'Введите правильно сумму',
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'step' => '0.01',
                'min' => '0',
            ],
        ]);
    }

    public function getParent(): string
    {
        return NumberType::class;
    }
}
