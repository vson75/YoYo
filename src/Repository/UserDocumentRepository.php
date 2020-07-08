<?php

namespace App\Repository;

use App\Entity\UserDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDocument[]    findAll()
 * @method UserDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDocument::class);
    }

    public function findDocumentByUserIdAndTypeDoc($user,$type){
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->andWhere('u.DocumentType = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findAllDocumentByUserId($user,$type){
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->andWhere('u.DocumentType = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return UserDocument[] Returns an array of UserDocument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserDocument
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
