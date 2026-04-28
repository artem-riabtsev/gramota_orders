<?php

namespace App\Tests\Integration;

use App\Entity\Product;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductIntegrationTest extends KernelTestCase
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
    
    private function createTestProject(): Project
    {
        $project = new Project();
        $project->setName('Тестовый проект');
        $this->entityManager->persist($project);
        return $project;
    }
    
    public function testProductPersistence(): void
    {
        $project = $this->createTestProject();
        $this->entityManager->flush();
        
        $product = new Product();
        $product->setDescription('Интеграционный продукт');
        $product->setDate(new \DateTimeImmutable('2024-01-01'));
        $product->setBasic(true);
        $product->setProject($project);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        $savedProduct = $this->entityManager->getRepository(Product::class)->find($product->getId());
        
        $this->assertNotNull($savedProduct);
        $this->assertEquals('Интеграционный продукт', $savedProduct->getDescription());
        $this->assertTrue($savedProduct->getBasic());
    }
    
    public function testProductBasicFilter(): void
    {
        $project = $this->createTestProject();
        $this->entityManager->flush();
        
        $basicProduct = new Product();
        $basicProduct->setDescription('Базовый продукт');
        $basicProduct->setDate(new \DateTimeImmutable());
        $basicProduct->setBasic(true);
        $basicProduct->setProject($project);
        $this->entityManager->persist($basicProduct);
        
        $nonBasicProduct = new Product();
        $nonBasicProduct->setDescription('Небазовый продукт');
        $nonBasicProduct->setDate(new \DateTimeImmutable());
        $nonBasicProduct->setBasic(false);
        $nonBasicProduct->setProject($project);
        $this->entityManager->persist($nonBasicProduct);
        
        $this->entityManager->flush();
        
        $basicProducts = $this->entityManager->getRepository(Product::class)
            ->findBy(['basic' => true]);
        
        $this->assertGreaterThanOrEqual(1, count($basicProducts));
    }
}
