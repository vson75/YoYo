<?php

namespace App\Repository;

use App\Entity\PostDateType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostDateType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostDateType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostDateType[]    findAll()
 * @method PostDateType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostDateTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostDateType::class);
    }

    // /**
    //  * @return PostDateType[] Returns an array of PostDateType objects
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
    public function findOneBySomeField($value): ?PostDateType
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
