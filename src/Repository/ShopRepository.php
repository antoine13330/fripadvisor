<?php

namespace App\Repository;

use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @extends ServiceEntityRepository<Shop>
 *
 * @method Shop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shop[]    findAll()
 * @method Shop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function save(Shop $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Shop $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Shop[] Returns an array of Shop objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Shop
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Retourne les boutiques actives, paginées par $page à la $limite
     * @param int $page
     * @param int $limit limite de boutiques par page
     */
    public function findShops(int $page, int $limit) {
        $qb = $this->createQueryBuilder('s');
        $qb->where($qb->expr()->eq('s.satus', "1"))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les boutiques actives par code postal, paginées par $page à la $limite
     * @param int $page
     * @param int $limit limite de boutiques par page
     * @param string $postalCode code postal
     */
    public function findShopsByLocation(int $page, int $limit, string $postalCode) {
        $qb = $this->createQueryBuilder('s');
        $qb->where($qb->expr()->eq('s.satus', "1"))
            ->andWhere($qb->expr()->eq('s.poastalCode', $postalCode))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
    public function findShopByCoordinates(float $lat,float $lon,float $rayon)
    {       
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Shop::class, 'shop');
        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT * 
            FROM `shop` 
            WHERE shop.satus = "1" AND (6378 * acos(cos(radians(:latitude)) * cos(radians(shop.latitude)) * cos(radians(shop.longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(shop.latitude)))) <= :rayon
            ORDER BY (6378 * acos(cos(radians(:latitude)) * cos(radians(shop.latitude)) * cos(radians(shop.longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(shop.latitude))))',
            $rsm
        );
        $query->setParameter('latitude', $lat);
        $query->setParameter('longitude', $lon);
        $query->setParameter('rayon', $rayon);
        return $query->getResult();
    }
}
