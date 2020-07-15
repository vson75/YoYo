<?php

namespace App\Repository;

use App\Entity\RequestOrganisationInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestOrganisationInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestOrganisationInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestOrganisationInfo[]    findAll()
 * @method RequestOrganisationInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestOrganisationInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestOrganisationInfo::class);
    }

    public function getDetailWaitingOrganisation(){
        return $this->createQueryBuilder('roi')
            ->andWhere('roi.RequestStatus = 1')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return RequestOrganisationInfo[] Returns an array of RequestOrganisationInfo objects
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
    public function findOneBySomeField($value): ?RequestOrganisationInfo
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
