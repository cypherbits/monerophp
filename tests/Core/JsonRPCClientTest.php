<?php declare(strict_types=1);
/**
 * PHP Test for testing the RPC implementation class
 * @author Cypherbits <info@avanix.es>
 * PHP8 porting and refactor
 */

namespace Core;

use MoneroIntegrations\MoneroPhp\Core\JsonRPCClient;
use PHPUnit\Framework\TestCase;

class JsonRPCClientTest extends TestCase
{

    private static JsonRPCClient $client;

    public function test__construct(): void
    {
        self::$client = new JsonRPCClient("https://ifconfig.co/", null, null, checkSSL: false, debug: true);
        self::assertNotNull(self::$client, 'JsonRPCClient $client is null');
    }

    /**
     * @depends test__construct
     */
    public function test_run(): void
    {
        $this->test__construct();

        $response = self::$client->getResponse('', '');
        echo $response;
        self::assertNotNull($response, 'Response is null');
    }
}
