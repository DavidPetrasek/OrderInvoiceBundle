<?php

namespace Psys\OrderInvoiceBundle\Repository;

use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceFinal>
 *
 * @method InvoiceFinal|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceFinal|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceFinal[]    findAll()
 * @method InvoiceFinal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceFinalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceFinal::class);
    }

    public function save(InvoiceFinal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceFinal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceFinal[] Returns an array of InvoiceFinal objects
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

//    public function findOneBySomeField($value): ?InvoiceFinal
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
