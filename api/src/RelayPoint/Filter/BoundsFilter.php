<?php

namespace App\RelayPoint\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Filter allowing to obtain the relay points according to the geographical limits passed in parameter
 * ex. /relay_points?bounds=4.507141,45.799127,5.086670,45.432190 (minLng, maxLat, maxLng, minLat).
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
final class BoundsFilter extends AbstractContextAwareFilter
{
    private $_bounds;

    public function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('bounds' != $property) {
            return;
        }

        $this->setBounds($value);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin($rootAlias.'.address', 'rpa')
            ->andWhere('rpa.latitude BETWEEN :minLat AND :maxLat')
            ->andWhere('rpa.longitude BETWEEN :minLng AND :maxLng')
            ->setParameter('minLat', floatval($this->_bounds[3]))
            ->setParameter('maxLat', floatval($this->_bounds[1]))
            ->setParameter('maxLng', floatval($this->_bounds[2]))
            ->setParameter('minLng', floatval($this->_bounds[0]))
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
                    'description' => 'Filter on relay points having its geographical coordinates inside the given area',
                    'name' => 'bounds',
                    'type' => 'array',
                ],
            ];
        }

        return $description;
    }

    private function setBounds(string $bounds)
    {
        $this->_bounds = explode(',', $bounds);

        if (4 !== count($this->_bounds)) {
            throw new BadRequestHttpException('The bounds parameter you passed is invalid!');
        }

        return $this;
    }
}
