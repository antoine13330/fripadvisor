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
     */
    public function findProducts(int $page, int $limit) {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->eq('p.status', "1"))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    public function findProductByFilter(int $size, int $price)
    {
        $res = $this->createQueryBuilder('P');
        $res->where($res->expr()->eq('P.size', $size));
        $res->andWhere($res->expr()->eq('P.price', $price));
        return $res->getQuery()->getResult();
    }
    public function findProductBySize(int $size)
    {
        $res = $this->createQueryBuilder('P');
        $res->where($res->expr()->eq('P.size', $size));
        return $res->getQuery()->getResult();
    }


    public function findProductByPrice(int $price)
    {
        $res = $this->createQueryBuilder('P');
        $res->andWhere($res->expr()->eq('P.price', $price));
        return $res->getQuery()->getResult();
    }
}
