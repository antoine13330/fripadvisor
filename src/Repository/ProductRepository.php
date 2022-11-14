<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Retourne les produits actifs, paginées par $page à la $limite
     * @param int $page
     * @param int $limit limite de produits par page
     * @return Request
     */
    public function findProducts(int $page, int $limit): Request {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->eq('p.status', true))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les produits actifs selon leur shop, paginées par $page à la $limite
     * @param int $page
     * @param int $limit limite de produits par page
     * @param int $shopId id du shop
     * @return Request
     */
    public function findProductsByShop(int $page, int $limit, int $shopId): Request {
        $qb = $this->createQueryBuilder('p');
        $qb->innerJoin('p.isShop', 's')
            ->where($qb->expr()->eq('p.status', true))
            ->andWhere($qb->expr()->eq('s.id', $shopId))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les produits actifs selon leur shop, paginées par $page à la $limite
     * @param int $page
     * @param int $limit limite de produits par page
     * @param int $categoryId id de la category
     * @return Request
     */
    public function findProductsByCategory(int $page, int $limit, int $categoryId): Request {
        $qb = $this->createQueryBuilder('p');
        $qb->innerJoin('p.categoryProduct', 'cp')
            ->where($qb->expr()->eq('p.status', true))
            ->andWhere($qb->expr()->eq('cp.id', $categoryId))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}
