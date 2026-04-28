<?php

namespace App\Tests\Integration;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Project;
use App\Entity\Payment;
use Brick\Money\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderIntegrationTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
    
    private function createTestCustomer(): Customer
    {
        $customer = new Customer();
        $customer->setName('Тестовый клиент');
        $customer->setEmail('test@integration.ru');
        $customer->setPhone('+79990001122');
        return $customer;
    }
    
    private function createTestProduct(): Product
    {
        $project = new Project();
        $project->setName('Интеграционный проект');
        $this->entityManager->persist($project);
        
        $product = new Product();
        $product->setDescription('Тестовый продукт');
        $product->setDate(new \DateTimeImmutable());
        $product->setProject($project);
        return $product;
    }
    
    public function testOrderCreationAndPersistence(): void
    {
        $customer = $this->createTestCustomer();
        $this->entityManager->persist($customer);
        $this->entityManager->flush();
        
        $order = new Order();
        $order->setCustomer($customer);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        
        $savedOrder = $this->entityManager->getRepository(Order::class)->find($order->getId());
        
        $this->assertNotNull($savedOrder);
        $this->assertEquals($customer->getId(), $savedOrder->getCustomer()->getId());
    }
    
    public function testOrderTotalRecalculatesAfterItemAdd(): void
    {
        $customer = $this->createTestCustomer();
        $this->entityManager->persist($customer);
        
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $order = new Order();
        $order->setCustomer($customer);
        $this->entityManager->persist($order);
        
        $item = new OrderItem();
        $item->setOrder($order);
        $item->setProduct($product);
        $item->setDescription('Позиция');
        $item->setQuantity(3);
        $item->setPrice(Money::of(250, 'RUB'));
        $item->setLineTotal(Money::of(750, 'RUB'));
        $order->addOrderItem($item);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        $this->assertEquals(750, $order->getOrderTotal()->getAmount()->toFloat());
    }
    
    public function testOrderStatusChangesAfterPayment(): void
    {
        $customer = $this->createTestCustomer();
        $this->entityManager->persist($customer);
        
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $order = new Order();
        $order->setCustomer($customer);
        $this->entityManager->persist($order);
        
        $item = new OrderItem();
        $item->setOrder($order);
        $item->setProduct($product);
        $item->setDescription('Позиция');
        $item->setQuantity(1);
        $item->setPrice(Money::of(1000, 'RUB'));
        $item->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($item);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        // Статус должен быть UNPAID (1)
        $this->assertEquals(1, $order->getStatus()->value);
        
        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setAmount(Money::of(1000, 'RUB'));
        $order->addPayment($payment);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        // Статус должен стать PAID (4)
        $this->assertEquals(4, $order->getStatus()->value);
    }
    
    public function testOrderTotalUpdatesWhenItemRemoved(): void
    {
        $customer = $this->createTestCustomer();
        $this->entityManager->persist($customer);
        
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $order = new Order();
        $order->setCustomer($customer);
        $this->entityManager->persist($order);
        
        $item = new OrderItem();
        $item->setOrder($order);
        $item->setProduct($product);
        $item->setDescription('Позиция');
        $item->setQuantity(2);
        $item->setPrice(Money::of(500, 'RUB'));
        $item->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($item);
        
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        $this->assertEquals(1000, $order->getOrderTotal()->getAmount()->toFloat());
        
        $order->removeOrderItem($item);
        $this->entityManager->flush();
        $this->entityManager->refresh($order);
        
        $this->assertEquals(0, $order->getOrderTotal()->getAmount()->toFloat());
    }
}
