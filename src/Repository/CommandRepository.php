<?php

namespace App\Repository;

use App\Entity\Command;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Command>
 */
class CommandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Command::class);
    }

    public function findAllCommandByClient(User $user, int $page, int $limit): array
    {
       return $this->createQueryBuilder('command')
           ->where('command.user = :user')
           ->setParameter('user', $user)
           ->orderBy('command.createdAt', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit)
           ->getQuery()
           ->getResult();
    }

    public function findAllSearch(string $search)
    {
        return $this->createQueryBuilder('c')
            ->where('c.firstName LIKE :search OR c.lastName LIKE :search OR c.city LIKE :search')
            ->setParameter('search', '%'.$search.'%')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // --- Pour l'admin : toutes les commandes payées ---

    public function findAllPaidCommands(int $page, int $limit): array
    {
        return $this->createQueryBuilder('command')
            ->leftJoin('command.commandItems', 'ci')
            ->addSelect('ci')
            ->andWhere('command.status = :paid')
            ->setParameter('paid', Command::STATUS_PAID)
            ->orderBy('command.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllCountByStatus(string $status): int
    {
       return $this->createQueryBuilder('command')
           ->select('count(command.id)')
           ->where('command.status = :status')
           ->setParameter('status', $status)
           ->getQuery()
           ->getSingleScalarResult();
    }

    public function findAllCountCommand(User $user)
    {
        return $this->createQueryBuilder('command')
            ->select('count(command.id)')
            ->where('command.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandsAdminUnread()
    {
        return $this->createQueryBuilder('command')
            ->select('count(command.id)')
            ->where('command.isRead = :isRead')
            ->andWhere('command.status = :status')
            ->setParameter('isRead', false)
            ->setParameter('status', Command::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCommandUserPending(User $user)
    {
        return $this->createQueryBuilder('command')
            ->select('count(command.id)')
            ->where('command.user = :user')
            ->andWhere('command.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Command::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Command[] Returns an array of Command objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Command
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
