<?php

namespace App\Repository;

use App\Entity\RequestOrganisationDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RequestOrganisationDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method RequestOrganisationDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method RequestOrganisationDocument[]    findAll()
 * @method RequestOrganisationDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestOrganisationDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestOrganisationDocument::class);
    }


    public function findLastDocumentByUserIdAndTypeDoc($user,$type){
        return $this->createQueryBuilder('rod')
            ->andWhere('rod.user = :user')
            ->andWhere('rod.DocumentType = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('rod.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    public function findAllDocumentByUserId($user,$type){
        return $this->createQueryBuilder('rod')
            ->andWhere('rod.user = :user')
            ->andWhere('rod.DocumentType = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('rod.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return RequestOrganisationDocument[] Returns an array of RequestOrganisationDocument objects
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
    public function findOneBySomeField($value): ?RequestOrganisationDocument
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
