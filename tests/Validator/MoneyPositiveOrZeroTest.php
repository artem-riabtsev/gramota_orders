<?php

namespace App\Tests\Validator;

use App\AppBundle\Validator\Constraints\MoneyPositiveOrZero;
use App\AppBundle\Validator\Constraints\MoneyPositiveOrZeroValidator;
use Brick\Money\Money;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MoneyPositiveOrZeroTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new MoneyPositiveOrZeroValidator();
    }

    public function testPositiveMoneyIsValid(): void
    {
        $this->validator->validate('10000', new MoneyPositiveOrZero());
        $this->assertNoViolation();
    }

    public function testZeroMoneyIsValid(): void
    {
        $this->validator->validate('0', new MoneyPositiveOrZero());
        $this->assertNoViolation();
    }

    public function testNegativeMoneyIsInvalid(): void
    {
        $this->validator->validate('-500', new MoneyPositiveOrZero());
        $this->buildViolation('Enter a positive number or zero')
            ->assertRaised();
    }
}
