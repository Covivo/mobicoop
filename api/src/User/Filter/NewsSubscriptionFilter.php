<?php

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class NewsSubscriptionFilter extends AbstractContextAwareFilter
{
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["{$property}"] = [
                'property' => $property,
                'type' => 'boolean',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter users who have subscribed to the newsletter',
                    'name' => 'newsSubscription',
                    'type' => 'boolean',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('newsSubscription' != $property) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere($rootAlias.'.newsSubscription = :param')
            ->setParameter('param', 1 === $value || 'true' === $value)
        ;
    }
}
