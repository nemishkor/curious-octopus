<?php
declare(strict_types=1);

namespace App\ValueObject;

class PauseRange {

    public function __construct(
        public int $min,
        public int $max,
    ) {
    }

}
