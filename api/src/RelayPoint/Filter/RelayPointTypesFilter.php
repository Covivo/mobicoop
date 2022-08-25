<?php

namespace App\RelayPoint\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Filter allowing to obtain the relay points according to the type or types of ID passed in parameters
 * ex. /relay_points?relayPointTypes=1,12.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
final class RelayPointTypesFilter extends AbstractContextAwareFilter
{
    private $_relayPointTypeIds;

    public function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('relayPointTypes' != $property) {
            return;
        }

        $this->setRelayPointTypes($value);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->leftJoin($rootAlias.'.relayPointType', 'rpt')
            ->andWhere('rpt.id IN (:ids)')
            ->setParameter('ids', $this->_relayPointTypeIds)
        ;
    }

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
                    'description' => 'Filter on relay points having the corresponding type(s)',
                    'name' => 'relayPointTypes',
                    'type' => 'array',
                ],
            ];
        }

        return $description;
    }

    private function setRelayPointTypes(string $types)
    {
        $this->_relayPointTypeIds = explode(',', $types);

        $this->_relayPointTypeIds = array_map(function ($relayPointType) {
            $id = intval($relayPointType);

            if (!is_int($id) || 0 === $id) {
                throw new BadRequestHttpException('The types parameter you passed is invalid!');
            }

            return $id;
        }, $this->_relayPointTypeIds);

        return $this;
    }
}
