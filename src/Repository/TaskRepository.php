<?php

namespace App\Repository;

use App\Entity\Task;
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

    public function findTasks(?TaskStatus $status, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate): array
    {
        $qb = $this->createQueryBuilder('t');

        $dateColumn = match ($status) {
            TaskStatus::FINISHED => 't.finishedAt',
            TaskStatus::DELETED => 't.deletedAt',
            default => 't.createdAt',
        };

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

        $qb->orderBy($dateColumn, 'ASC');

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
