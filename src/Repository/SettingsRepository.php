<?php

namespace Psys\OrderInvoiceBundle\Repository;

use Psys\OrderInvoiceBundle\Entity\InvoiceSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceSettings>
 *
 * @method InvoiceSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceSettings[]    findAll()
 * @method InvoiceSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingsRepository extends ServiceEntityRepository
{
    use SettingsTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceSettings::class);
    }

    public function save(InvoiceSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceSettings $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceSettings[] Returns an array of InvoiceSettings objects
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

//    public function findOneBySomeField($value): ?InvoiceSettings
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
