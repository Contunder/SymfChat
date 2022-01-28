<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Ldap\Security\LdapUser;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Search vaults
     * @param string $id
     * @return array
     **/
    public function groupsOfUser(string $id): array
    {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.user_to_groups','d')
            ->where('d.username = :id')
            ->setParameter('id', $id);

        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * Search vaults
     * @param int $id
     * @return array
     **/
    public function oneGroup(int $id): array
    {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.messages','m')
            ->where('g.id = :id')
            ->setParameter('id', $id);

        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * @param int $id
     * @return string
     **/
    public function lastSeen(int $id): string
    {

        $timestamp = $this->find($id)->getUpdatedAt()->getTimestamp();
        $strTime = array("seconde(s)", "minute(s)", "heure(s)", "jour", "mois", "année");
        $length = array("60","60","24","30","12","10");

        $currentTime = time();
        if($currentTime >= $timestamp) {

            $diff = time()- $timestamp;

            for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            if ($diff < 59 && $strTime[$i] == "second") {
                return 'Active';
            }else {
                return $diff . " " . $strTime[$i] ;
            }
        }
        return '';
    }

}