<?php
declare(strict_types=1);

namespace App\ValueObject;

readonly class PauseRange {

    public function __construct(
        public int $min,
        public int $max,
    ) {
    }

}
