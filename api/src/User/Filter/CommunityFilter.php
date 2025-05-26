<?php

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;

final class CommunityFilter extends AbstractContextAwareFilter {
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($property != "community") {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin($rootAlias .'.communityUsers', 'cu')
            ->andWhere('cu.community =:communityId')
            ->setParameter('communityId', $value)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                  'property' => $property,
                  'type' => 'string',
                  'required' => false,
                  'swagger' => [
                      'description' => 'Filter on users who are in the given community',
                      'name' => 'Community',
                      'type' => 'string',
                  ],
              ];
        }

        return $description;
    }
}
