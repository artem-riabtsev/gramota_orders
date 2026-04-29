<?php

namespace App\Tests\Entity;

use App\Entity\Price;
use App\Entity\Product;
use App\Entity\Project;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
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
    
    public function testPriceCreation(): void
    {
        $price = new Price();
        $price->setDescription('Тестовая позиция прайса');
        $price->setPrice(Money::of(5000, 'RUB'));
        $price->setProduct($this->createTestProduct());
        
        $this->assertEquals('Тестовая позиция прайса', $price->getDescription());
        $this->assertEquals(5000, $price->getPrice()->getAmount()->toFloat());
        $this->assertNotNull($price->getProduct());
    }
    
    public function testPriceValueInKopeks(): void
    {
        $price = new Price();
        $price->setPrice(Money::of(123.45, 'RUB'));
        
        $this->assertEquals(123.45, $price->getPrice()->getAmount()->toFloat());
    }
}
