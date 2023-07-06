<?php

namespace App;

use App\ValueObject\PauseRange;
use DateInterval;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

readonly class DaemonBreaker {

    private int $startTime;
    private int $timeLimit;
    private int $memoryLimit;

    public function __construct(
        private PauseRange|int $pause,
        DateInterval $timeLimit = new DateInterval('PT1H'),
        string $memoryLimit = '128M',
        private ?LoggerInterface $logger = null,
    ) {
        $this->startTime = time();
        $reference = new DateTimeImmutable;
        $this->timeLimit = $reference->add($timeLimit)->getTimestamp() - $reference->getTimestamp();
        $this->memoryLimit = $this->convertToBytes($memoryLimit);
    }

    private function convertToBytes(string $memoryLimit): int {
        $memoryLimit = strtolower($memoryLimit);
        $max = ltrim($memoryLimit, '+');
        if (str_starts_with($max, '0x')) {
            $max = intval($max, 16);
        } else {
            if (str_starts_with($max, '0')) {
                $max = intval($max, 8);
            } else {
                $max = (int)$max;
            }
        }

        switch (substr(rtrim($memoryLimit, 'b'), -1)) {
            case 't':
                $max *= 1024;
            // no break
            case 'g':
                $max *= 1024;
            // no break
            case 'm':
                $max *= 1024;
            // no break
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    public function isOkToGo(): bool {
        return time() - $this->startTime <= $this->timeLimit && memory_get_usage(true) <= $this->memoryLimit;
    }

    public function next(): void {
        $seconds = is_int($this->pause) ? $this->pause : rand($this->pause->min, $this->pause->max);
        $this->logger?->debug(sprintf('wait %ss', $seconds));
        sleep($seconds);
    }

}
