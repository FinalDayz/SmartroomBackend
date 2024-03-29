<?php

namespace App\Repository;

use App\Entity\Reading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reading|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reading|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reading[]    findAll()
 * @method Reading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reading::class);
    }

    /**
     * @return Reading[]
     */
    public function getLastReadingEachType(): array
    {

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('App\Entity\Reading', 'r');
        $rsm->addFieldResult('r', 'id', 'id');
        $rsm->addFieldResult('r', 'type', 'type');
        $rsm->addFieldResult('r', 'time', 'time');
        $rsm->addFieldResult('r', 'value', 'value');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT r.id, r.type, r.time, r.value
                    FROM reading r
                    JOIN (
                        SELECT type, max(id) maxTime
                            from reading
                            group by type
                        ) maxReading 
                    on r.type = maxReading.type
                    where r.id = maxReading.maxTime',
            $rsm
        );

        return $query->getResult();
    }

    public function lastInsertTime(): ?Reading
    {
        return $this->findOneBy([], ["time" => "desc"]);
    }

    public function getMaxMinTimeInterval(string $type, int $intervalSec, int $limit = 60)
    {
        $limit = min($limit, 100);
        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('time', 'timestamp', 'integer');
        $rsm->addScalarResult('max(r.value)', 'max', 'float');
        $rsm->addScalarResult('min(r.value)', 'min', 'float');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT unix_timestamp(r.time) as time, max(r.value), min(r.value)
                    FROM reading r
                    where r.type = :type
                    group by (unix_timestamp(r.time) - (unix_timestamp(r.time)%(:intervalSec)))
                    ORDER BY r.time DESC
                    limit :limit',
            $rsm
        );
        $query->setParameter("type", $type);
        $query->setParameter("intervalSec", $intervalSec);
        $query->setParameter("limit", $limit);

        return $query->getResult();
    }

    // /**
    //  * @return Reading[] Returns an array of Reading objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reading
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
