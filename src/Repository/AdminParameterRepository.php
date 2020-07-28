<?php

namespace App\Repository;

use App\Entity\AdminParameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdminParameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminParameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminParameter[]    findAll()
 * @method AdminParameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminParameter::class);
    }

    public function findLastestParamId(){
        return $this->createQueryBuilder('a')
            ->andWhere('a.managementFees is not NULL')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
            ;
    }
    // /**
    //  * @return AdminParameter[] Returns an array of AdminParameter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AdminParameter
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
