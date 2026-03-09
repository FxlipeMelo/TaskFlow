<?php

namespace App\Repository;

use App\Entity\Priority;
use App\Entity\Task;
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

    public function add(Task $task): Task
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $connection->beginTransaction();

        try {
            $entityManager->persist($task);
            $entityManager->flush();

            $connection->commit();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            if (isset($task) && $entityManager->contains($task)) {
                $entityManager->detach($task);
            }
            throw $e;
        }
        return $task;
    }

    public function update(Task $task, bool $flush = false): Task
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $entityManager->persist($task);
            $entityManager->flush();
            $connection->commit();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            if (isset($task) && $entityManager->contains($task)) {
                $entityManager->detach($task);
            }
            throw $e;
        }
        return $task;
    }

    public function delete(Task $task, bool $flush = false): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

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
