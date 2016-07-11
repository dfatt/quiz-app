<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class QuestionRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->createQueryBuilder('q')
            ->getQuery()
            ->getArrayResult();
    }
}
