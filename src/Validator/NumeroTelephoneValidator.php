<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NumeroTelephoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var NumeroTelephone $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        // N° téléphone commençant par un zéro, sans caractères blanc et une longueur maximale de 10 caractères.
        if (!preg_match('/^0\d{9}$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
