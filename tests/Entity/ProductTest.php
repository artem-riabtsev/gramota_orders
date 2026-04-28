<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Project;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private function createTestProject(): Project
    {
        $project = new Project();
        $project->setName('Тестовый проект');
        return $project;
    }
    
    public function testProductCreation(): void
    {
        $product = new Product();
        $product->setDescription('Тестовый продукт');
        $product->setDate(new \DateTimeImmutable());
        $product->setBasic(true);
        $product->setProject($this->createTestProject());
        
        $this->assertEquals('Тестовый продукт', $product->getDescription());
        $this->assertTrue($product->getBasic());
        $this->assertNotNull($product->getProject());
    }
    
    public function testProductBasicFlag(): void
    {
        $product = new Product();
        $product->setBasic(true);
        $this->assertTrue($product->getBasic());
        
        $product->setBasic(false);
        $this->assertFalse($product->getBasic());
    }
    
    public function testProductHasProject(): void
    {
        $product = new Product();
        $project = $this->createTestProject();
        $product->setProject($project);
        
        $this->assertSame($project, $product->getProject());
    }
    
    public function testProductDateIsImmutable(): void
    {
        $product = new Product();
        $date = new \DateTimeImmutable('2024-01-01');
        $product->setDate($date);
        
        $this->assertEquals('2024-01-01', $product->getDate()->format('Y-m-d'));
    }
}
