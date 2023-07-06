<?php
declare(strict_types=1);

namespace App\Validator;

use App\Entity\Database;
use App\Service\Dbal;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PingDatabaseValidator extends ConstraintValidator {

    public function __construct(private readonly Dbal $dbal) {
    }

    public function validate(mixed $value, Constraint $constraint) {
        if (!$value instanceof Database) {
            throw new UnexpectedValueException($value, Database::class);
        }
        if (!$constraint instanceof PingDatabase) {
            throw new UnexpectedValueException($constraint, PingDatabase::class);
        }
        if (!$this->dbal->ping($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }

}
