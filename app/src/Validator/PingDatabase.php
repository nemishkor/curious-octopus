<?php
declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class PingDatabase extends Constraint {

    public string $message = 'Unable to connect to the database';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
