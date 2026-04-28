<?php

namespace App\Tests\Integration;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomerIntegrationTest extends KernelTestCase
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
    
    public function testCustomerPersistence(): void
    {
        $customer = new Customer();
        $customer->setName('Интеграционный клиент');
        $customer->setEmail('int@test.ru');  // Добавлено
        $customer->setPhone('+79991112233');
        
        $this->entityManager->persist($customer);
        $this->entityManager->flush();
        
        $savedCustomer = $this->entityManager->getRepository(Customer::class)->find($customer->getId());
        
        $this->assertNotNull($savedCustomer);
        $this->assertEquals('Интеграционный клиент', $savedCustomer->getName());
    }
    
    public function testCustomerWithOrders(): void
    {
        $customer = new Customer();
        $customer->setName('Клиент с заказами');
        $customer->setEmail('orders@test.ru');  // Добавлено
        $customer->setPhone('+79991112233');     // Добавлено
        $this->entityManager->persist($customer);
        
        $order1 = new Order();
        $order1->setCustomer($customer);
        $this->entityManager->persist($order1);
        
        $order2 = new Order();
        $order2->setCustomer($customer);
        $this->entityManager->persist($order2);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($customer);
        
        $this->assertCount(2, $customer->getOrders());
        $this->assertTrue($customer->hasOrders());
    }
}
