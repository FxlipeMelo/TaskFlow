<?php

namespace App\Tests\Functional\UI\Controller;

use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        $this->entityManager->close();
        parent::tearDown();
    }

    private function createUserAndLogin($client): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $client->loginUser($user);

        return $user;
    }

    public function testTaskListRequiresLogin(): void
    {
        $this->client->request('GET', '/task');

        $this->assertResponseRedirects('/login');
    }

    public function testTaskListIsAccessibleForLoggedInUser(): void
    {
        $this->createUserAndLogin($this->client);

        $this->client->request('GET', '/task');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Task list');
    }
}
