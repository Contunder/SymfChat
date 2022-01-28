<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * Search vaults
     * @param int $User_Id
     * @return array
     */
    public function userInGroup(int $User_Id): Array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.groups','c')
            ->where('c.id = :id')
            ->setParameter('id', $User_Id);

        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * Search vaults
     * @param string $username
     * @return array
     */
    public function user(string $username): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username);

        $query = $qb->getQuery();
        return $query->execute();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

}
