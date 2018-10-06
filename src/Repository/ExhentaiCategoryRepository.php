<?php

namespace App\Repository;

use App\Entity\ExhentaiCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiCategory[]    findAll()
 * @method ExhentaiCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiCategoryRepository extends ExHentaiRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiCategory::class);
    }

//    /**
//     * @return ExhentaiCategory[] Returns an array of ExhentaiCategory objects
//     */
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
    public function findOneBySomeField($value): ?ExhentaiCategory
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
