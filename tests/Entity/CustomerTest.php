<?php

namespace App\Tests\Entity;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testCustomerCreation(): void
    {
        $customer = new Customer();
        $customer->setName('Иванов Иван');
        $customer->setEmail('ivan@test.ru');
        $customer->setPhone('+79991234567');
        
        $this->assertEquals('Иванов Иван', $customer->getName());
        $this->assertEquals('ivan@test.ru', $customer->getEmail());
        $this->assertEquals('+79991234567', $customer->getPhone());
    }
    
    public function testCustomerHasNoOrdersByDefault(): void
    {
        $customer = new Customer();
        $this->assertFalse($customer->hasOrders());
    }
}
