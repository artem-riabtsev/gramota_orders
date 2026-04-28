<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetOrdersList(): void
    {
        $this->client->request('GET', '/api/order/list?limit=5');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateOrder(): void
    {
        $this->client->request('POST', '/api/order/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customerId' => 1
        ]));
        $this->assertResponseIsSuccessful();
    }

    public function testGetSingleOrder(): void
    {
        $this->client->request('GET', '/api/order/1');
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteOrderNotFound(): void
    {
        $this->client->request('DELETE', '/api/order/99999/delete');
        $this->assertResponseStatusCodeSame(404);
    }
}
