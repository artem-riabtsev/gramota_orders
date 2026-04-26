<?php

namespace App\Tests\Integration;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Payment;
use Brick\Money\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PaymentIntegrationTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
    }
    
    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
    
    private function createTestOrder(): Order
    {
        $customer = new Customer();
        $customer->setName('Клиент для платежей');
        $customer->setEmail('payment@test.ru');
        $customer->setPhone('+79991112233');
        $this->entityManager->persist($customer);
        
        $order = new Order();
        $order->setCustomer($customer);
        $this->entityManager->persist($order);
        
        return $order;
    }
    
    public function testPaymentPersistence(): void
    {
        $order = $this->createTestOrder();
        $this->entityManager->flush();
        
        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setAmount(Money::of(5000, 'RUB'));
        
        // Добавляем через заказ
        $order->addPayment($payment);
        
        $this->entityManager->flush();
        
        $savedPayment = $this->entityManager->getRepository(Payment::class)->find($payment->getId());
        
        $this->assertNotNull($savedPayment);
        $this->assertEquals(5000, $savedPayment->getAmount()->getAmount()->toFloat());
        $this->assertEquals($order->getId(), $savedPayment->getOrder()->getId());
    }
    
    public function testMultiplePaymentsToSameOrder(): void
    {
        $order = $this->createTestOrder();
        $this->entityManager->flush();
        
        $payment1 = new Payment();
        $payment1->setOrder($order);
        $payment1->setAmount(Money::of(300, 'RUB'));
        $order->addPayment($payment1);
        
        $payment2 = new Payment();
        $payment2->setOrder($order);
        $payment2->setAmount(Money::of(200, 'RUB'));
        $order->addPayment($payment2);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        $totalPaid = $order->getTotalPaid()->getAmount()->toFloat();
        $this->assertEquals(500, $totalPaid);
    }
}
