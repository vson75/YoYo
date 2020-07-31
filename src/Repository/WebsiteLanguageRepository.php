<?php

namespace App\Repository;

use App\Entity\WebsiteLanguage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WebsiteLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebsiteLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebsiteLanguage[]    findAll()
 * @method WebsiteLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebsiteLanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteLanguage::class);
    }

    // /**
    //  * @return WebsiteLanguage[] Returns an array of WebsiteLanguage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WebsiteLanguage
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
