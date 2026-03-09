<?php

namespace App\Repository;

use App\Entity\Priority;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Priority>
 */
class PriorityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Priority::class);
    }

    public function add(Priority $input): Priority
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        try {
            $priority = new Priority();
            $priority->setName($input->getName());
            $entityManager->persist($priority);
            $entityManager->flush();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            if (isset($priority) && $entityManager->contains($priority)) {
                $entityManager->detach($priority);
            }
            throw $e;
        }
        return $priority;
    }

    public function remove(Priority $priority, bool $flush = false)
    {
        $this->getEntityManager()->remove($priority);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function update(Priority $priority, bool $flush = false): void
    {
        $this->getEntityManager()->persist($priority);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Priority[] Returns an array of Priority objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Priority
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
