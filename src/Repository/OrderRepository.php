<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findVisibleToUser(User $user): array
    {
        // Base query with eager loading
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.createdBy', 'u')
            ->addSelect('u')
            ->leftJoin('o.orderItems', 'oi')
            ->addSelect('oi')
            ->leftJoin('oi.product', 'p')
            ->addSelect('p')
            ->leftJoin('o.deliveries', 'd')
            ->addSelect('d')
            ->orderBy('o.id', 'DESC');

        // Admins see all orders
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $qb->getQuery()->getResult();
        }

        // Staff/users see only their own orders
        return $qb
            ->where('o.createdBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.createdBy', 'u')
            ->addSelect('u')
            ->leftJoin('o.orderItems', 'oi')
            ->addSelect('oi')
            ->leftJoin('oi.product', 'p')
            ->addSelect('p')
            ->leftJoin('o.deliveries', 'd')
            ->addSelect('d')
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}