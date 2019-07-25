<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use UVDesk\CommunityPackages\UVDesk\ECommerce\Entity\ECommerceOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ECommerceOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ECommerceOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ECommerceOrder[]    findAll()
 * @method ECommerceOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ECommerceOrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ECommerceOrder::class);
    }

    // /**
    //  * @return ECommerceOrder[] Returns an array of ECommerceOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ECommerceOrder
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
