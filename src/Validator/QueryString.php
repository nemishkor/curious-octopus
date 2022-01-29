<?php
declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class QueryString extends Constraint {

    public string $invalidQueryMessage = 'Query string is invalid: {{ message }}';
    public string $tooManyStatementsMessage = 'Found too many statements: {{ count }}. Expected 1 only';
    public string $unsupportedQueryMessage = 'Unsupported type of query. Only SELECT queries are allowed';

}
