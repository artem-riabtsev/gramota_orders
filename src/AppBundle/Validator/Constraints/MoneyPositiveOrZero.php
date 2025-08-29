<?php

namespace App\AppBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class MoneyPositiveOrZero extends Constraint
{
    public $message;

    public function __construct(
        string $message = 'Enter a positive number or zero',
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
        $this->message = $message;
    }
}
