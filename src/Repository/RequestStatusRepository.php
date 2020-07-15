<?php

namespace App\Repository;

use App\Entity\RequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestStatus[]    findAll()
 * @method RequestStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestStatus::class);
    }

    // /**
    //  * @return RequestStatus[] Returns an array of RequestStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RequestStatus
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
