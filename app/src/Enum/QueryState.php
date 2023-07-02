<?php
declare(strict_types=1);

namespace App\Enum;

abstract class QueryState {

    public const IN_QUEUE = 'in_queue';
    public const SCRAPPING = 'scrapping';
    public const COMPILING = 'compiling';
    public const DONE = 'done';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';

}
