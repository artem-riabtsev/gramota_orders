<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\Customer;
use App\Entity\Payment;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    private function createTestOrder(): Order
    {
        $customer = new Customer();
        $customer->setName('Тестовый клиент');
        
        $order = new Order();
        $order->setCustomer($customer);
        
        return $order;
    }
    
    public function testPaymentCreation(): void
    {
        $payment = new Payment();
        $payment->setOrder($this->createTestOrder());
        $payment->setAmount(Money::of(1000, 'RUB'));
        
        $this->assertEquals(1000, $payment->getAmount()->getAmount()->toFloat());
        $this->assertNotNull($payment->getDate());
        $this->assertNotNull($payment->getOrder());
    }
    
    public function testPaymentDateIsImmutable(): void
    {
        $payment = new Payment();
        $date = new \DateTimeImmutable('2024-01-15');
        $payment->setDate($date);
        
        $this->assertEquals('2024-01-15', $payment->getDate()->format('Y-m-d'));
    }
    
    public function testPaymentAmountInKopeks(): void
    {
        $payment = new Payment();
        $payment->setOrder($this->createTestOrder());
        $payment->setAmount(Money::of(999.99, 'RUB'));
        
        $this->assertEquals(999.99, $payment->getAmount()->getAmount()->toFloat());
    }
}
