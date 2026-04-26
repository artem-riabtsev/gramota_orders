<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerApiTest extends WebTestCase
{
    public function testSearchCustomerByEmail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/customer/search?q=test@example.com');
        $this->assertResponseIsSuccessful();
    }

    public function testSearchCustomerByPhone(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/customer/search?q=79991234567');
        $this->assertResponseIsSuccessful();
    }
    
    public function testSearchCustomerByName(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/customer/search?q=Тестовый');
        $this->assertResponseIsSuccessful();
    }
}
