<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Task;
use App\Domain\Enum\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testNewTaskHasOpenStatus(): void
    {
        $task = new Task();
        $this->assertEquals(TaskStatus::OPEN, $task->getStatus());
    }

    public function testMarkAsFinished(): void
    {
        $task = new Task();
        $this->assertNull($task->getFinishedAt());

        $task->markAsFinished();

        $this->assertEquals(TaskStatus::FINISHED, $task->getStatus());
        $this->assertNotNull($task->getFinishedAt());
    }

    public function testMarkAsDeleted(): void
    {
        $task = new Task();
        $this->assertNull($task->getDeletedAt());

        $task->markAsDeleted();

        $this->assertEquals(TaskStatus::DELETED, $task->getStatus());
        $this->assertNotNull($task->getDeletedAt());
    }

    public function testReopenDeletedTask(): void
    {
        $task = new Task();
        $task->markAsDeleted();
        $task->reopen();

        $this->assertEquals(TaskStatus::OPEN, $task->getStatus());
        $this->assertNull($task->getDeletedAt());
    }

    public function testReopenFinishedTask(): void
    {
        $task = new Task();
        $task->markAsFinished();
        $task->reopen();

        $this->assertEquals(TaskStatus::OPEN, $task->getStatus());
        $this->assertNull($task->getFinishedAt());
    }
}
