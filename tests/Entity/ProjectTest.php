<?php

namespace App\Tests\Entity;

use App\Entity\Project;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testProjectCreation(): void
    {
        $project = new Project();
        $project->setName('Основной проект');
        
        $this->assertEquals('Основной проект', $project->getName());
    }
}
