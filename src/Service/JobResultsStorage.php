<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Query;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class JobResultsStorage {

    public function __construct(
        private string $resultsDir,
        private Filesystem $filesystem,
        private SerializerInterface $serializer,
    ) {
    }

    public function saveAsJson(Query $query, array $results): void {
        $this->filesystem->dumpFile(
            sprintf('%s/%s.json', $this->resultsDir, $query->getId()),
            $this->serializer->serialize($results, JsonEncoder::FORMAT),
        );
    }

}
