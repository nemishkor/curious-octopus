<?php
declare(strict_types=1);

namespace App\Messages;

class ProcessJobMessage {

    public function __construct(private int $jobId) {
    }

    public function getJobId(): int {
        return $this->jobId;
    }

}
