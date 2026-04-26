<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportApiTest extends WebTestCase
{
    public function testReportWithDateFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/report/order-items?dateFrom=2026-04-01&dateTo=2026-04-30');
        $this->assertResponseIsSuccessful();
    }

    public function testReportWithStatusFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/report/order-items?status=1');
        $this->assertResponseIsSuccessful();
    }
}
