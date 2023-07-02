<?php
declare(strict_types=1);

namespace App\Validator;

use Exception;
use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class QueryStringValidator extends ConstraintValidator {

    public function validate(mixed $value, Constraint $constraint) {
        if (!$constraint instanceof QueryString) {
            throw new UnexpectedValueException($constraint, PingDatabase::class);
        }
        if ($value === null) {
            return;
        }
        try {
            $parser = new Parser($value);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ParserException $exception) {
            $this->context
                ->buildViolation($constraint->invalidQueryMessage, ['{{ message }}' => $exception->getMessage()])
                ->addViolation();
            return;
        }
        if (count($parser->errors) > 0) {
            $message = implode(' | ', array_map(fn(Exception $e) => $e->getMessage(), $parser->errors));
            $this->context
                ->buildViolation($constraint->invalidQueryMessage, ['{{ message }}' => $message,])->addViolation();
            return;
        }
        if (count($parser->statements) > 1) {
            $this->context
                ->buildViolation($constraint->tooManyStatementsMessage, ['{{ count }}', count($parser->statements)])
                ->addViolation();
            return;
        }
        if (!$parser->statements[0] instanceof SelectStatement) {
            $this->context
                ->buildViolation($constraint->unsupportedQueryMessage)
                ->addViolation();
        }
    }

}
