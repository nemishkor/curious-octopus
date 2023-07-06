<?php
declare(strict_types=1);

namespace App\Messages;

readonly class CompileQueryResultsMessage {

    public function __construct(private int $queryId) {
    }

    public function getQueryId(): int {
        return $this->queryId;
    }

}
