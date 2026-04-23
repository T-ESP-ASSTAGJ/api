<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class Base64Validator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Base64) {
            throw new UnexpectedTypeException($constraint, Base64::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $base64 = preg_replace('/^data:[a-z\/+]+;base64,/', '', $value);

        if (!preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $base64)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode('INVALID_BASE64_ERROR')
                ->addViolation()
            ;

            return;
        }

        if (false === base64_decode($base64, strict: true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode('INVALID_BASE64_ERROR')
                ->addViolation()
            ;
        }
    }
}
