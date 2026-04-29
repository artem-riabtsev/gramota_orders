<?php

namespace App\AppBundle\Validator\Constraints;

use Brick\Money\Money;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MoneyPositiveOrZeroValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MoneyPositiveOrZero) {
            throw new UnexpectedTypeException($constraint, MoneyPositiveOrZero::class);
        }

        // пользовательские ограничения должны игнорировать пустые значения и null, чтобы
        // позволить другим ограничениям (NotBlank, NotNull, и др.) позаботиться об этом
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // вызовите это исключение, если ваш валидатор не может обработать переданный тип, чтобы он мог быть отмечен как невалидный
            throw new UnexpectedValueException($value, 'string');
        }

        // Проверка, что сумма положительная или больше 0
        if (Money::ofMinor($value, 'RUB')->isPositiveOrZero()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
