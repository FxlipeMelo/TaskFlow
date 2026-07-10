<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function add(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function update(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    public function findTasks(?TaskStatus $status, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, ?int $workspaceId = null, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.workspace', 'w')
            ->innerJoin('w.users', 'u');

        $dateColumn = match ($status) {
            TaskStatus::FINISHED => 't.finishedAt',
            TaskStatus::DELETED => 't.deletedAt',
            default => 't.createdAt',
        };

        if ($user !== null) {
            $qb->andWhere('u = :user')
                ->setParameter('user', $user);
        }

        if ($status !== null) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if ($startDate !== null) {
            $qb->andWhere($dateColumn . ' >= :startDate')->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere($dateColumn . ' <= :endDate')->setParameter('endDate', $endDate);
        }

        if ($workspaceId !== null) {
            $qb->andWhere('w.id = :workspaceId')
                ->setParameter('workspaceId', $workspaceId);
        }

        $qb->orderBy($dateColumn, 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function countTasks(?TaskStatus $status): int
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('COUNT(t.id)');

        if ($status !== null) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countTasksByCategory(): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('IDENTITY(t.category) as category, COUNT(t.id) as total')
            ->andWhere('t.status != :status')
            ->setParameter('status', TaskStatus::DELETED)
            ->groupBy('t.category');

        return $qb->getQuery()->getResult();
    }

    public function getFinishedTasksFromLast7Days(): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('t.finishedAt')
            ->andWhere('t.status = :status')
            ->setParameter('status', TaskStatus::FINISHED)
            ->andWhere('t.finishedAt >= :limitDate')
            ->setParameter('limitDate', new \DateTimeImmutable('-6 days'));

        return $qb->getQuery()->getResult();
    }

    public function findTasksByContext(User $user, ?int $activeWorkspaceId = null, TaskStatus $status = TaskStatus::OPEN): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.workspace', 'w')
            ->innerJoin('w.users', 'u')
            ->andWhere('u = :user')
            ->setParameter('user', $user)
            ->andWhere('t.status = :status')
            ->setParameter('status', $status);

        if ($activeWorkspaceId !== null) {
            $qb->andWhere('w.id = :workspaceId')
                ->setParameter('workspaceId', $activeWorkspaceId);
        }

        $qb->orderBy('t.id', 'DESC');

        return $qb->getQuery()->getResult();

    }
    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
