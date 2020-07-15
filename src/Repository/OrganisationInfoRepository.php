<?php

namespace App\Repository;

use App\Entity\OrganisationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrganisationInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganisationInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganisationInfo[]    findAll()
 * @method OrganisationInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganisationInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationInfo::class);
    }

    // /**
    //  * @return OrganisationInfo[] Returns an array of OrganisationInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrganisationInfo
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
