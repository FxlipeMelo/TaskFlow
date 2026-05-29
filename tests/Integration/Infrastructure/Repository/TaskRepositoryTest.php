<?php

namespace App\Tests\Integration\Infrastructure\Repository;

use App\Domain\Entity\Task;
use App\Domain\Entity\Category;
use App\Domain\Entity\Priority;
use App\Domain\Entity\User;
use App\Domain\Enum\TaskStatus;
use App\Infrastructure\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class TaskRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->taskRepository = $this->entityManager->getRepository(Task::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testFindTasksWithRelations(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $this->entityManager->persist($user);

        $category = new Category();
        $category->setName('Test Category');
        $this->entityManager->persist($category);

        $priority = new Priority();
        $priority->setName('High');
        $this->entityManager->persist($priority);

        $task = new Task();
        $task->setName('Test Task');
        $task->setUser($user);
        $task->setCategory($category);
        $task->setPriority($priority);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $tasks = $this->taskRepository->findTasksWithRelations(['status' => TaskStatus::OPEN]);

        $this->assertCount(1, $tasks);
        $this->assertEquals('Test Task', $tasks[0]->getName());
        $this->assertEquals('Test Category', $tasks[0]->getCategory()->getName());
        $this->assertEquals('High', $tasks[0]->getPriority()->getName());
        $this->assertEquals('test@example.com', $tasks[0]->getUser()->getEmail());
    }
}
