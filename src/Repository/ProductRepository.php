<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findVisibleToUser(User $user): array
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->findAll();
        }

        return $this->createQueryBuilder('p')
            ->leftJoin('p.createdBy', 'u')
            ->where('p.createdBy = :user OR u.roles LIKE :adminRole')
            ->setParameter('user', $user)
            ->setParameter('adminRole', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();
    }
}
