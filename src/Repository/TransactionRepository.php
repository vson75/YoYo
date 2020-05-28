<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }



    public function getTotalAmountbyPost($val)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.post = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function getTransactionbyPost($val)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.post = :val')
            ->andWhere('t.anonymousDonation = 0')
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getAnonymousTransactionbyPost($val)
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->andWhere('t.post = :val')
            ->andWhere('t.anonymousDonation = 1')
            ->setParameter('val', $val)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
    // /**
    //  * @return Transaction[] Returns an array of Transaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
