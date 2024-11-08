<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findBySortWithPaginationAndSearch(
        string $orderBy, 
        string $direction, 
        int $page = 1, 
        int $limit = 10, 
        string $price = '', 
        string $stockQuantity = '', 
        string $createdAt = ''
    ) {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.' . $orderBy, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    
        if (!empty($price)) {
            $queryBuilder->andWhere('p.price = :price')
                         ->setParameter('price', $price);
        }
    
        if (!empty($stockQuantity)) {
            $queryBuilder->andWhere('p.stockQuantity = :stockQuantity')
                         ->setParameter('stockQuantity', $stockQuantity);
        }
    
        if (!empty($createdAt)) {
            $createdAtObj = \DateTime::createFromFormat('Y-m-d', $createdAt);
            
            if ($createdAtObj === false) {
                throw new \InvalidArgumentException('Invalid date format. Please use YYYY-MM-DD.');
            }
    
            $createdAtObj->setTime(0, 0, 0);
    
            $queryBuilder->andWhere('p.createdDate >= :createdAtStart')
                         ->andWhere('p.createdDate < :createdAtEnd')
                         ->setParameter('createdAtStart', $createdAtObj->format('Y-m-d 00:00:00'))
                         ->setParameter('createdAtEnd', $createdAtObj->format('Y-m-d 23:59:59'));
        }
    
        $query = $queryBuilder->getQuery();
        $products = $query->getResult();
    
        // Get total products count for pagination, including filters
        $totalQuery = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');
    
        if (!empty($price)) {
            $totalQuery->andWhere('p.price = :price')
                       ->setParameter('price', $price);
        }
        if (!empty($stockQuantity)) {
            $totalQuery->andWhere('p.stockQuantity = :stockQuantity')
                       ->setParameter('stockQuantity', $stockQuantity);
        }
        if (!empty($createdAt)) {
            $totalQuery->andWhere('p.createdDate >= :createdAtStart')
                       ->andWhere('p.createdDate < :createdAtEnd')
                       ->setParameter('createdAtStart', $createdAtObj->format('Y-m-d 00:00:00'))
                       ->setParameter('createdAtEnd', $createdAtObj->format('Y-m-d 23:59:59'));
        }
    
        $totalResults = $totalQuery->getQuery()->getSingleScalarResult();
    
        return [
            'products' => $products,
            'totalResults' => $totalResults
        ];
    }

    // public function findBySort($orderBy = 'createdDate', $direction = 'desc')
    // {
    //     $qb = $this->createQueryBuilder('p');
        
    //     $validColumns = ['name', 'price', 'stockQuantity', 'createdDate'];
    //     if (!in_array($orderBy, $validColumns)) {
    //         $orderBy = 'createdDate';
    //     }
    
    //     $validDirection = ['asc', 'desc'];
    //     if (!in_array($direction, $validDirection)) {
    //         $direction = 'desc';
    //     }

    //     return $qb->orderBy("p.$orderBy", $direction)
    //               ->getQuery()
    //               ->getResult();
    // }
}
