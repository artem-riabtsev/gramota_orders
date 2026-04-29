<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentApiTest extends WebTestCase
{
    public function testCreatePayment(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/payment/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'orderId' => 1
        ]));
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('paymentId', $data);
    }
    
    public function testDeletePayment(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/payment/1/delete');
        // Может быть 200 или 404, но не 500
        $this->assertLessThan(500, $client->getResponse()->getStatusCode());
    }
}
