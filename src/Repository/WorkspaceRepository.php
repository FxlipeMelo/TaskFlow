<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workspace>
 */
class WorkspaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workspace::class);
    }

    public function add(Workspace $workspace, Bool $flush = false): void
    {
        $this->getEntityManager()->persist($workspace);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Workspace $workspace, Bool $flush = false): void
    {
        $this->getEntityManager()->remove($workspace);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function update(Workspace $workspace, Bool $flush = false): void
    {
        $this->getEntityManager()->persist($workspace);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWorkspacesByUser(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('w');

        $qb->innerJoin('w.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('w.name', 'ASC');

        return $qb;
    }

    //    /**
    //     * @return Workspace[] Returns an array of Workspace objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Workspace
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
