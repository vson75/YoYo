<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostStatus;
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
             ->andWhere('p.status = 3 OR p.status = 4')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
            ;
    }

    // the syntax ?sting --> the string can be null
    public function findAllWithSearch(?string $value){
        $qb = $this->createQueryBuilder('p');

            if($value){
                $qb->andWhere('p.title LIKE :val OR p.content LIKE :val')
                    ->setParameter('val','%'.$value.'%');
            }
            return $qb->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;

    }

    public function findAllExpiredDate(){
        $status_collecting = PostStatus::POST_COLLECTING;

        $date_now = new \DateTime("now");
        $date_now = $date_now->format("yy-m-d");
        return $this->createQueryBuilder('p')
            ->andWhere('p.finishAt = :date_now')
            ->andWhere('p.status = :status')
            ->setParameter('date_now', $date_now)
            ->setParameter('status', $status_collecting)
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
