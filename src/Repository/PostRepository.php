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
        $status_Collecting = PostStatus::POST_COLLECTING;
        $status_finish_collect = PostStatus::POST_FINISH_COLLECTING;
        $status_transfert_fund = PostStatus::POST_TRANSFERT_FUND;

         return   $this->publishedAtIsNotNull()
            ->andWhere('p.status = '.$status_Collecting.' ')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPostFinishCollect(){
        $status_finish_collect = PostStatus::POST_FINISH_COLLECTING;
        $status_transfert_fund = PostStatus::POST_TRANSFERT_FUND;

        return   $this->publishedAtIsNotNull()
            ->andWhere('p.status = '.$status_finish_collect.' or p.status = '.$status_transfert_fund.'')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPostByFavorite($user, bool $collecting)
    {
        $status_Collecting = PostStatus::POST_COLLECTING;
        $status_finish_collect = PostStatus::POST_FINISH_COLLECTING;
        $status_transfert_fund = PostStatus::POST_TRANSFERT_FUND;

        $qb= $this->publishedAtIsNotNull();
        if($collecting){
            $qb->andWhere('p.status = '.$status_Collecting.' ');
        }else{
            $qb->andWhere('p.status = '.$status_finish_collect.' or p.status = '.$status_transfert_fund.'');
        }
        return   $qb->andWhere('fav.user = :user')
                    ->andWhere('fav.isFavorite = 1')
                    ->innerJoin('p.favorites','fav')
                    ->setParameter('user', $user)
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
        //add 1 day after the finish At
        $date_now = $date_now->modify('+1 day');
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

    public function findAllPostByStatus($status_collecting){
       // $status_collecting = PostStatus::POST_WAITING_VALIDATION;
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status_collecting)
            ->getQuery()
            ->getResult()
            ;
    }

    public function countDistinctPostByStatus($status_collecting){
        //$status_collecting = PostStatus::POST_WAITING_VALIDATION;
        return $this->createQueryBuilder('p')
            ->select('count( distinct p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status_collecting)
            ->getQuery()
            ->getSingleScalarResult()
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
