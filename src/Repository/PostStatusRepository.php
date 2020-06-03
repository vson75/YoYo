<?php

namespace App\Repository;

use App\Entity\PostStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostStatus[]    findAll()
 * @method PostStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostStatus::class);
    }

    // /**
    //  * @return PostStatus[] Returns an array of PostStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PostStatus
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
