<?php

namespace App\Solidary\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Solidary\Entity\Solidary;
use Doctrine\ORM\QueryBuilder;

class SolidaryExtension extends SolidaryTerritory implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null)
    {
        if (Solidary::class == $resourceClass && 'ADMIN_get' === $operationName) {
            $this->addWhere($queryBuilder);
        }
    }

    public function addWhere(QueryBuilder $queryBuilder)
    {
        $territories = $this->_territoryOperatorManager->getOperatorTerritories([]);

        if (count($territories) > 0) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->leftJoin($rootAlias.'.solidaryUserStructure', 'sus_0')
                ->leftJoin('sus_0.solidaryUser', 'su_0')
                ->leftJoin('su_0.user', 'u_0')
                ->leftJoin('u_0.addresses', 'autfe')
                ->leftJoin('autfe.territories', 'atutfe')
                ->andWhere('(autfe.home = 1 AND atutfe.id in (:territories))')
                ->setParameter('territories', $territories)
            ;
        }
    }
}
