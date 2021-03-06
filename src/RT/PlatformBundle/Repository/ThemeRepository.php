<?php

namespace RT\PlatformBundle\Repository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ThemeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ThemeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Find all products
     *
     * @param int $page
     * @param int $max
     * @return Paginator
     */
    public function findAll($page = 0, $max = 5)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->setFirstResult(($page) * $max)
            ->setMaxResults($max);


        return new Paginator($qb);
    }
}
