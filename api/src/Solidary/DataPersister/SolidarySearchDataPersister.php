<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Service\SolidaryManager;

final class SolidarySearchDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryManager;
    
    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidarySearch;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'transport') {
            $data = $this->solidaryManager->getSolidaryTransportSearchResults($data);
        } elseif (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'carpool') {
            $data = $this->solidaryManager->getSolidaryCarpoolSearchSearchResults($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
