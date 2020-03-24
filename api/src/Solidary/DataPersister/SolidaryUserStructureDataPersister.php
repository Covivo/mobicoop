<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Service\SolidaryUserStructureManager;

final class SolidaryUserStructureDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryUserStructureManager;
    
    public function __construct(SolidaryUserStructureManager $solidaryUserStructureManager)
    {
        $this->solidaryUserStructureManager = $solidaryUserStructureManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryUserStructure;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'put') {
            $data = $this->solidaryUserStructureManager->updateSolidaryUserStructure($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
