<?php

namespace App\Tests\Entity;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\Project;
use App\Config\OrderStatus;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private function createTestProduct(): Product
    {
        $project = new Project();
        $project->setName('Тестовый проект');
        
        $product = new Product();
        $product->setDescription('Тестовый продукт');
        $product->setDate(new \DateTimeImmutable());
        $product->setProject($project);
        
        return $product;
    }
    
    private function createTestOrder(): Order
    {
        $customer = new Customer();
        $customer->setName('Тестовый клиент');
        $customer->setEmail('test@test.com');
        $customer->setPhone('79991112233');
        
        $order = new Order();
        $order->setCustomer($customer);
        
        return $order;
    }
    
    public function testOrderStatusEmptyWhenNoItems(): void
    {
        $order = $this->createTestOrder();
        $this->assertEquals(OrderStatus::EMPTY, $order->getStatus());
    }
    
    public function testOrderStatusChangesWhenItemAdded(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setProduct($this->createTestProduct());
        $orderItem->setDescription('Тестовая позиция');
        $orderItem->setQuantity(1);
        $orderItem->setPrice(Money::of(1000, 'RUB'));
        $orderItem->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem);
        
        $this->assertEquals(OrderStatus::UNPAID, $order->getStatus());
    }
    
    public function testOrderTotalCalculatedCorrectly(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem1 = new OrderItem();
        $orderItem1->setOrder($order);
        $orderItem1->setProduct($this->createTestProduct());
        $orderItem1->setDescription('Позиция 1');
        $orderItem1->setQuantity(2);
        $orderItem1->setPrice(Money::of(500, 'RUB'));
        $orderItem1->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem1);
        
        $orderItem2 = new OrderItem();
        $orderItem2->setOrder($order);
        $orderItem2->setProduct($this->createTestProduct());
        $orderItem2->setDescription('Позиция 2');
        $orderItem2->setQuantity(1);
        $orderItem2->setPrice(Money::of(2000, 'RUB'));
        $orderItem2->setLineTotal(Money::of(2000, 'RUB'));
        $order->addOrderItem($orderItem2);
        
        $order->culculateOrderTotal();
        
        $this->assertEquals(3000, $order->getOrderTotal()->getAmount()->toFloat());
    }
    
    public function testTotalPaidCalculatedCorrectly(): void
    {
        $order = $this->createTestOrder();
        
        $payment1 = new Payment();
        $payment1->setOrder($order);  // Добавлено
        $payment1->setAmount(Money::of(500, 'RUB'));
        $order->addPayment($payment1);
        
        $payment2 = new Payment();
        $payment2->setOrder($order);  // Добавлено
        $payment2->setAmount(Money::of(300, 'RUB'));
        $order->addPayment($payment2);
        
        $order->culculateTotalPaid();
        
        $this->assertEquals(800, $order->getTotalPaid()->getAmount()->toFloat());
    }
    
    public function testStatusChangesToPartiallyPaidWhenPartialPayment(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setProduct($this->createTestProduct());
        $orderItem->setDescription('Позиция');
        $orderItem->setQuantity(1);
        $orderItem->setPrice(Money::of(1000, 'RUB'));
        $orderItem->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem);
        
        $payment = new Payment();
        $payment->setOrder($order);  // Добавлено
        $payment->setAmount(Money::of(500, 'RUB'));
        $order->addPayment($payment);
        
        $this->assertEquals(OrderStatus::PARTIALLY_PAID, $order->getStatus());
    }
    
    public function testStatusChangesToPaidWhenFullyPaid(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setProduct($this->createTestProduct());
        $orderItem->setDescription('Позиция');
        $orderItem->setQuantity(1);
        $orderItem->setPrice(Money::of(1000, 'RUB'));
        $orderItem->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem);
        
        $payment = new Payment();
        $payment->setOrder($order);  // Добавлено
        $payment->setAmount(Money::of(1000, 'RUB'));
        $order->addPayment($payment);
        
        $this->assertEquals(OrderStatus::PAID, $order->getStatus());
    }
    
    public function testStatusChangesToOverpaidWhenExcessPayment(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setProduct($this->createTestProduct());
        $orderItem->setDescription('Позиция');
        $orderItem->setQuantity(1);
        $orderItem->setPrice(Money::of(1000, 'RUB'));
        $orderItem->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem);
        
        $payment1 = new Payment();
        $payment1->setOrder($order);  // Добавлено
        $payment1->setAmount(Money::of(1000, 'RUB'));
        $order->addPayment($payment1);
        
        $payment2 = new Payment();
        $payment2->setOrder($order);  // Добавлено
        $payment2->setAmount(Money::of(200, 'RUB'));
        $order->addPayment($payment2);
        
        $this->assertEquals(OrderStatus::OVERPAID, $order->getStatus());
    }
    
    public function testOrderTotalUpdatesWhenItemRemoved(): void
    {
        $order = $this->createTestOrder();
        
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setProduct($this->createTestProduct());
        $orderItem->setDescription('Позиция');
        $orderItem->setQuantity(2);
        $orderItem->setPrice(Money::of(500, 'RUB'));
        $orderItem->setLineTotal(Money::of(1000, 'RUB'));
        $order->addOrderItem($orderItem);
        
        $this->assertEquals(1000, $order->getOrderTotal()->getAmount()->toFloat());
        
        $order->removeOrderItem($orderItem);
        
        $this->assertEquals(0, $order->getOrderTotal()->getAmount()->toFloat());
    }
}
