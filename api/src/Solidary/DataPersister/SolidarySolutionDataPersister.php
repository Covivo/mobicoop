<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Service\SolidarySolutionManager;

final class SolidarySolutionDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidarySolutionManager;

    public function __construct(SolidarySolutionManager $solidarySolutionManager)
    {
        $this->solidarySolutionManager = $solidarySolutionManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidarySolution;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->solidarySolutionManager->createSolidarySolution($data);
        } elseif (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'formalRequest') {
            $data = $this->solidarySolutionManager->makeFormalRequest($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
