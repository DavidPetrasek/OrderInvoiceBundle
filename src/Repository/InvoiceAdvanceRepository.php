<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Repository;

use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceAdvance>
 *
 * @method InvoiceAdvance|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceAdvance|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceAdvance[]    findAll()
 * @method InvoiceAdvance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceAdvanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceAdvance::class);
    }

    public function save(InvoiceAdvance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceAdvance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceAdvance[] Returns an array of InvoiceAdvance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvoiceAdvance
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
