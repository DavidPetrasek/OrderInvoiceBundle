<?php

namespace Psys\OrderInvoiceBundle\Repository;

use Psys\OrderInvoiceBundle\Entity\InvoiceSeller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceSeller>
 *
 * @method InvoiceSeller|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceSeller|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceSeller[]    findAll()
 * @method InvoiceSeller[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceSellerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceSeller::class);
    }

    public function save(InvoiceSeller $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceSeller $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceSeller[] Returns an array of InvoiceSeller objects
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

//    public function findOneBySomeField($value): ?InvoiceSeller
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
