<?php
declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseControllerTest extends WebTestCase {

    use JsonAssertions;

    public function postDatabaseBadRequestDataProvider(): array {
        return [
            [[]],
            [['host' => 'database.url', 'user' => 'root', 'password' => 'root']],
            [['host' => 'database.url', 'user' => 'root', 'name' => 'foo']],
            [['host' => 'database.url', 'password' => 'root', 'name' => 'foo']],
            [['user' => 'root', 'password' => 'root', 'name' => 'foo']],
            [['host' => '', 'user' => 'root', 'password' => 'root', 'name' => 'foo']],
            [['host' => 'database.url', 'user' => '', 'password' => 'root', 'name' => 'foo']],
            [['host' => 'database.url', 'user' => 'root', 'password' => '', 'name' => 'foo']],
            [['host' => 'database.url', 'user' => 'root', 'password' => 'root', 'name' => '']],
        ];
    }

    public function testPostDatabaseBadRequest(array $requestData) {
        $client = self::createClient();
        $client->jsonRequest('POST', '/api/databases', $requestData);
        self::assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testPostDatabase() {
        $client = self::createClient();
        $client->jsonRequest(
            'POST',
            '/api/databases',
            ['host' => 'database.url', 'user' => 'root', 'password' => 'root', 'name' => 'foo']
        );
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertJsonDocumentMatchesSchema(
            $client->getResponse()->getContent(),
            [
                'type' => 'object',
                'required' => ['host', 'user', 'name'],
                'properties' => [
                    'host' => ['type' => 'string'],
                    'user' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                ],
            ]
        );
    }

    public function testGetDatabases() {
    }

    public function testDeleteDatabase() {
    }

}
