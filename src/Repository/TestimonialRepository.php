<?php

namespace App\Repository;

use App\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    public function findAllPaginated(int $currentPage, int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->where('t.isPublished = :isPublished')
            ->setParameter('isPublished', false)
            ->setFirstResult(($currentPage - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllCount()
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAverageRating(): float
    {
        return $this->createQueryBuilder('t')
            ->select('AVG(t.rating)')
            ->where('t.isPublished = :isPublished ')
            ->setParameter('isPublished', false)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    //    /**
    //     * @return Testimonial[] Returns an array of Testimonial objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Testimonial
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
