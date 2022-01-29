<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Database;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use SodiumException;

class Dbal {

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

}
