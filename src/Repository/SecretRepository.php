<?php

namespace App\Repository;

use App\Entity\Secret;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Secret>
 */
class SecretRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Secret::class);
    }

    public function findValidSecret(string $hash): ?Secret
    {
        $qb = $this->createQueryBuilder('secret')
            ->where('secret.hash = :hash')
            ->setParameter('hash', $hash);
        $result = $qb->getQuery()->getResult();
        $result = array_filter($result, function ($secret) {
            $now = new \DateTimeImmutable();
            $expireDate = $secret->getCreatedAt()->modify('+' . $secret->getExpireAfter() . ' minutes');
            if ($secret->getExpireAfterViews() > 0 && ($now < $expireDate || $secret->getCreatedAt() === $expireDate)) {
                return true;
            }
            return false;
        });

        return array_shift($result);
    }
}
