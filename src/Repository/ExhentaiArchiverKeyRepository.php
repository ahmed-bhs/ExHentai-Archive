<?php

namespace App\Repository;

use App\Entity\ExhentaiArchiverKey;
use App\Entity\ExhentaiTagNamespace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiArchiverKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiArchiverKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiArchiverKey[]    findAll()
 * @method ExhentaiArchiverKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiArchiverKeyRepository extends ExHentaiRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiArchiverKey::class);
    }

    public function findOneOrCreate(array $criteria, object $entity)
    {
        /** @var ExhentaiArchiverKey $existingEntity */
        $existingEntity = parent::findOneOrCreate($criteria, $entity);

        if($existingEntity->getTime() < new \DateTime("-12 hour")) {
            $existingEntity->setToken($entity->getToken());
            $this->_em->merge($entity);
        }

        return $existingEntity;
    }

//    /**
//     * @return ExhentaiArchiverKey[] Returns an array of ExhentaiArchiverKey objects
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
    public function findOneBySomeField($value): ?ExhentaiArchiverKey
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
