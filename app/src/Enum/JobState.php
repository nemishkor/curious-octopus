<?php
declare(strict_types=1);

namespace App\Enum;

abstract class JobState {

    public const IN_QUEUE = 'in_queue';
    public const IN_PROGRESS = 'in_progress';
    public const DONE = 'done';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';

}
