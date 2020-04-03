<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryCarpoolSearch;
use App\Solidary\Service\SolidaryManager;

final class SolidaryCarpoolSearchDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryManager;
    
    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryCarpoolSearch;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->solidaryManager->getSolidaryCarpoolSearchSearchResults($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
