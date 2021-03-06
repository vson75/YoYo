<?php

namespace App\Repository;

use App\Entity\DocumentType;
use App\Entity\Post;
use App\Entity\PostDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostDocument[]    findAll()
 * @method PostDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostDocument::class);
    }

    public function findLastestDocumentByPostAndType(Post $post, $documentType)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.post = :val and p.documentType =:docType')
            ->setParameter('val', $post)
            ->setParameter('docType', $documentType)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return PostDocument[] Returns an array of PostDocument objects
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
    public function findOneBySomeField($value): ?PostDocument
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
