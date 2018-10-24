<?php

namespace App\Carpool\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class LocalityFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {      
        
        switch ($property) {
            case 'startLocality' :
                $queryBuilder
                ->join('o.points', 'startPoint')
                ->join('startPoint.address', 'startAddress')
                ->andWhere('startPoint.position = 0')
                ->andWhere('startAddress.addressLocality = :startLocality')
                ->setParameter('startLocality', $value);
                break;
            case 'destinationLocality':
                $queryBuilder
                ->join('o.points', 'destinationPoint')
                ->join('destinationPoint.address', 'destinationAddress')
                ->andWhere('destinationPoint.lastPoint = 1')
                ->andWhere('destinationAddress.addressLocality = :destinationLocality')
                ->setParameter('destinationLocality', $value);
                break;
        }
    }
    
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }
        
        $description = [];
        foreach ($this->properties as $property => $strategy) {
            switch ($property) {
                case 'startLocality' :
                    $description["startLocality"] = [
                            'property' => $property,
                            'type' => 'string',
                            'required' => false,
                            'swagger' => [
                                    'description' => 'startLocality',
                                    'name' => 'startLocality',
                                    'type' => 'string',
                            ],
                    ];
                    break;
                case 'destinationLocality':
                    $description["destinationLocality"] = [
                            'property' => $property,
                            'type' => 'string',
                            'required' => false,
                            'swagger' => [
                                    'description' => 'destinationLocality',
                                    'name' => 'destinationLocality',
                                    'type' => 'string',
                            ],
                    ];
                    break;
            }
        }
        
        return $description;
    }
}