<?php

namespace App\User\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\User\Entity\User;
use Doctrine\ORM\QueryBuilder;

class IdentityProofFilter extends AbstractContextAwareFilter
{
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
                    'description' => 'Filter for identityProof status',
                    'name' => 'identityProofStatus',
                    'type' => 'integer',
                ],
            ];
        }

        return $description;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('identityProofStatus' != $property) {
            return;
        }

        $queryBuilder
            ->join('u.identityProofs', 'ip')
            ->andWhere('u.status != :userStatus')
            ->setParameter('userStatus', User::STATUS_PSEUDONYMIZED)
        ;

        if (is_array($value)) {
            $queryBuilder
                ->andWhere('ip.status IN (:status)')
                ->setParameter('status', implode(', ', $value))
            ;
        } else {
            $queryBuilder
                ->andWhere('ip.status = :status')
                ->setParameter('status', $value)
            ;
        }
    }
}
