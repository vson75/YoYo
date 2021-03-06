<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    //User have 5h max to create or reset his password
    public function findOneForResetCreatePassword($id,$token,$tokencreateAt): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT user.id
                        from user
                        where user.tokencreate_at is not null and TIMEDIFF(now(), :tokenCreateAt) < "24:00"
                        and user.id = :id
                        and user.token not like 0
                        and user.token = :token';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'tokenCreateAt' => $tokencreateAt,
            'id' => $id,
            'token' => $token
            ]);
        return $stmt->fetchAll();
    }

    public function NumberWaitingOrganisation(){
        return $this->createQueryBuilder('u')
            ->select('count(distinct u.id)')
            ->andWhere('u.askOrganisation = 1')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }


    public function checkUserIsCreatingOrganisation($value){
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :val')
            ->andWhere('u.askOrganisation = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAdminUserByASC(){
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :roleAdmin')
            ->setParameter('roleAdmin', '%ROLE_ADMIN%')
            ->groupBy('u.id')
            ->orderBy('u.id','ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?User
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
