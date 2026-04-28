<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderFlowTest extends WebTestCase
{
    public function testCompleteOrderFlow(): void
    {
        $client = static::createClient();

        // 1. Создаём заказ
        $client->request('POST', '/api/order/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customerId' => 1
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $orderId = $data['orderId'];

        // 2. Добавляем позицию
        $client->request('POST', '/api/order-item/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'orderId' => $orderId,
            'description' => 'Тестовая позиция',
            'productId' => 1,
            'quantity' => 2,
            'price' => 500
        ]));

        $this->assertResponseIsSuccessful();

        // 3. Проверяем, что заказ существует и имеет правильную сумму
        $client->request('GET', "/api/order/{$orderId}");
        $this->assertResponseIsSuccessful();
        $order = json_decode($client->getResponse()->getContent(), true);

        // Сумма заказа должна быть 1000 (2 * 500)
        $this->assertEquals(1000, $order['orderTotal']);

        // 4. Добавляем платеж
        $client->request('POST', '/api/payment/create', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'orderId' => $orderId
        ]));
        $this->assertResponseIsSuccessful();

        // 5. Проверяем, что заказ появился в списке
        $client->request('GET', '/api/order/list');
        $this->assertResponseIsSuccessful();
        $list = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($list['data']);
    }
}
