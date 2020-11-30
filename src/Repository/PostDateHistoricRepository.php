<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostDateHistoric;
use DateTime;
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


    public function selectDistinctByDate(Post $post, $postDateType){

        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT (pdh.date) FROM 
                post_date_historic pdh WHERE pdh.post_id = :post and pdh.post_date_type_id = :postDateType
                Order by pdh.date DESC
                ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'post' => $post->getId(),
            'postDateType' => $postDateType
        ]);
        return $stmt->fetchAll();
    }

    public function findPostDateHistoricByPost(Post $post, $postDateType,?string $date)
    {
        $qb = $this ->createQueryBuilder('p')
                    ->andWhere('p.post = :post and p.postDateType = :postDateType');

        if(!is_null($date)){
            $qb->andWhere('p.date = :date')
                ->setParameter('date', $date);
        }
        return $qb ->setParameter('post', $post)
            ->setParameter('postDateType', $postDateType)
            ->orderBy('p.id', 'DESC')
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
