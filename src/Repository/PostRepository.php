<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostSearch;
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
        $status_Collecting = PostStatus::POST_COLLECTING;
        $status_transfert_fund = PostStatus::POST_TRANSFERT_FUND;
         return   $this->publishedAtIsNotNull()
             ->andWhere('p.status = '.$status_Collecting.' OR p.status = '.$status_transfert_fund.'')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByUserWithPostSearch($user, PostSearch $search)
    {
        // dump($queryBuilder);die;
        // use the function publishedAtIsNotNull
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere('p.user = :user')
            ->setParameter('user',$user);

        if($search->getPostTitle()){
            $qb->andWhere('p.title like :title')
                ->setParameter('title', '%'.$search->getPostTitle().'%');
        }
        if($search->getStatus()){
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $search->getStatus());
        }

        return $qb->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;

    }

    // the syntax ?sting --> the string can be null
    public function findAllWithSearch(PostSearch $search){
        $qb = $this->createQueryBuilder('p');


        if($search->getPostTitle()){
            $qb->andWhere('p.title like :title')
                ->setParameter('title', '%'.$search->getPostTitle().'%');
        }
        if($search->getStatus()){
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $search->getStatus());
        }
       // dd($search);
            return $qb->orderBy('p.id', 'DESC')
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
