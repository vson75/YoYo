<?php

namespace App\Repository;

use App\Entity\OrganisationDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrganisationDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganisationDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganisationDocument[]    findAll()
 * @method OrganisationDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganisationDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationDocument::class);
    }

    // /**
    //  * @return OrganisationDocument[] Returns an array of OrganisationDocument objects
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
    public function findOneBySomeField($value): ?OrganisationDocument
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
