<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Article\Repository;

use App\Article\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Section|null find($id, $lockMode = null, $lockVersion = null)
 * @method Section|null findOneBy(array $criteria, array $orderBy = null)
 * @method Section[]    findAll()
 * @method Section[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Section::class);
    }

    public function find(int $id): ?Section
    {
        return $this->repository->find($id);
    }

    /**
     * Find the following section of a given section.
     *
     * @param Section $section
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNext(Section $section)
    {
        $query = $this->repository->createQueryBuilder('s')
        ->andWhere('s.position = :position')
        ->andWhere('s.article = :article')
        ->setParameter('position', $section->getPosition()+1)
        ->setParameter('article', $section->getArticle())
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }

    /**
     * Find the previous section of a given section.
     *
     * @param Section $section
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPrevious(Section $section)
    {
        $query = $this->repository->createQueryBuilder('s')
        ->andWhere('s.position = :position')
        ->andWhere('s.article = :article')
        ->setParameter('position', $section->getPosition()-1)
        ->setParameter('article', $section->getArticle())
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }
}
