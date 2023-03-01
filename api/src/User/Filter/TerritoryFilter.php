<?php

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class TerritoryFilter extends AbstractContextAwareFilter
{
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["{$property}"] = [
                'property' => $property,
                'type' => 'array',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter on user that have its home address in the given territory',
                    'name' => 'territory',
                    'type' => 'array',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('territory' != $property) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->leftJoin($rootAlias.'.addresses', 'homeAddress')
            ->leftJoin('homeAddress.territories', 't')
            ->andWhere('t.id = :territory')
            ->setParameter('territory', $value)
        ;
    }
}
