<?php
/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
 */

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 *  Filter for get members who dont have valid proposal.
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 */
final class ProposalValidFilter extends AbstractContextAwareFilter
{
    /**
     * @var EntityRepository
     */
    private $_proposalRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_proposalRepository = $entityManager->getRepository(Proposal::class);
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on users who dont have proposals or inactive users',
                    'name' => 'ProposalValid',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('proposalValidUntil' != $property) {
            return;
        }
        // we sanitize the value to be sure it's an int and not an iri
        if (strrpos($value, '/')) {
            $value = substr($value, strrpos($value, '/') + 1);
        }

        $usersWithALeastOneProposal = $this->_proposalRepository->createQueryBuilder('p')
            ->select('distinct(u.id)')
            ->join('p.user', 'u')
            ->where('p.private = 0')
            ->getQuery()
            ->getResult()
        ;
        $mergedUsersWithALeastOneProposal = call_user_func_array('array_merge', $usersWithALeastOneProposal);

        $queryBuilder
            ->leftJoin('u.proposals', 'p2')
            ->leftJoin('p2.criteria', 'c')
            ->andWhere("COALESCE(c.toDate,c.fromDate) <= '{$value}' OR ".$queryBuilder->expr()->notIn('u.id', $mergedUsersWithALeastOneProposal))
        ;
    }
}
