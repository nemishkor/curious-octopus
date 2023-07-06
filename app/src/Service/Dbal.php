<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Database;
use App\Entity\Query;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use SodiumException;

readonly class Dbal {

    public function __construct(
        private Encryptor $encryptor,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws SodiumException
     */
    public function ping(Database $database): bool {
        try {
            return $this->getConnection($database)->connect();
        } catch (Exception $e) {
            $this->logger->notice('Unable to connect to the database', ['exception' => $e]);
            return false;
        }
    }

    /**
     * @throws Exception
     * @throws SodiumException
     */
    private function getConnection(Database $database): Connection {
        return DriverManager::getConnection([
            'dbname' => $database->getName(),
            'user' => $database->getUser(),
            'password' => $this->encryptor->decrypt($database->getPassword()),
            'host' => $database->getHost(),
            'driver' => 'pdo_mysql',
        ]);
    }

    /**
     * @throws Exception
     * @throws SodiumException
     */
    public function query(Database $database, Query $query) {
        return $this->getConnection($database)->executeQuery($query->getString())->fetchAllAssociative();
    }

}
