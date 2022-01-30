<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\Query;
use App\Enum\JobState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Laminas\Code\Reflection\ClassReflection;
use ReflectionClassConstant;

/**
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Job::class);
    }

    public function getQueryJobCountsByState(Query $query): array {
        $results = $this->_em->createQueryBuilder()
            ->select('j.state', 'COUNT(j.id) c')
            ->from($this->_entityName, 'j')
            ->where('j.query = :query')
            ->setParameter('query', $query)
            ->groupBy('j.state')
            ->getQuery()
            ->getArrayResult();
        $countsByState = [];
        foreach ($results as $row) {
            $countsByState[$row['state']] = $row['c'];
        }
        foreach ((new ClassReflection(JobState::class))->getConstants(ReflectionClassConstant::IS_PUBLIC) as $state) {
            $countsByState[$state] = $countsByState[$state] ?? 0;
        }

        return $countsByState;
    }

}
