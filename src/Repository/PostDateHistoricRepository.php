<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostDateHistoric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PostDateHistoric|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostDateHistoric|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostDateHistoric[]    findAll()
 * @method PostDateHistoric[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostDateHistoricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostDateHistoric::class);
    }


    public function findPostDateHistoricByPost(Post $post, $postDateType)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.post = :post and p.postDateType = :postDateType')
            ->setParameter('post', $post)
            ->setParameter('postDateType', $postDateType)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return PostDateHistoric[] Returns an array of PostDateHistoric objects
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
    public function findOneBySomeField($value): ?PostDateHistoric
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
