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

use App\Article\Entity\Paragraph;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method Paragraph|null find($id, $lockMode = null, $lockVersion = null)
 * @method Paragraph|null findOneBy(array $criteria, array $orderBy = null)
 * @method Paragraph[]    findAll()
 * @method Paragraph[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParagraphRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Paragraph::class);
    }

    public function find(int $id): ?Paragraph
    {
        return $this->repository->find($id);
    }

    /**
     * Find the following paragraph of a given paragraph.
     *
     * @param Paragraph $paragraph
     * @return Paragraph
     */
    public function findNext(Paragraph $paragraph): Paragraph
    {
        $query = $this->repository->createQueryBuilder('p')
        ->andWhere('p.position = :position')
        ->andWhere('p.section = :section')
        ->setParameter('position', $paragraph->getPosition()+1)
        ->setParameter('section', $paragraph->getSection())
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }

    /**
     * Find the previous paragraph of a given paragraph.
     *
     * @param Paragraph $paragraph
     * @return Paragraph
     */
    public function findPrevious(Paragraph $paragraph): Paragraph
    {
        $query = $this->repository->createQueryBuilder('p')
        ->andWhere('p.position = :position')
        ->andWhere('p.section = :section')
        ->setParameter('position', $paragraph->getPosition()-1)
        ->setParameter('section', $paragraph->getSection())
        ->getQuery();
        
        return $query->getOneOrNullResult()
        ;
    }
}
