<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }


    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder=null){
        //it means that if a QueryBuilder object is passed, return that QueryBuilder object.
        // If a QueryBuilder object is not passed, then create one.
        return $queryBuilder ?: $this->createQueryBuilder('p');
    }


    // example a function can reuse anywhere in this Repository
    private function publishedAtIsNotNull(QueryBuilder $queryBuilder=null){
        return $this->getOrCreateQueryBuilder($queryBuilder)
                    ->andWhere('p.publishedAt IS NOT NULL');
    }


    public function findPostByNewest()
    {
       // dump($queryBuilder);die;
        // use the function publishedAtIsNotNull
         return   $this->publishedAtIsNotNull()
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return Post[] Returns an array of Post objects
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
    public function findOneBySomeField($value): ?Post
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
