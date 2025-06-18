<?php

namespace Psys\OrderInvoiceBundle\Repository;

use Psys\OrderInvoiceBundle\Entity\InvoiceBuyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceBuyer>
 *
 * @method InvoiceBuyer|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceBuyer|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceBuyer[]    findAll()
 * @method InvoiceBuyer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceBuyerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceBuyer::class);
    }

    public function save(InvoiceBuyer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceBuyer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceBuyer[] Returns an array of InvoiceBuyer objects
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

//    public function findOneBySomeField($value): ?InvoiceBuyer
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
