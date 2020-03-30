<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Service\SolidaryAskManager;

final class SolidaryAskDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryAskManager;

    public function __construct(SolidaryAskManager $solidaryAskManager)
    {
        $this->solidaryAskManager = $solidaryAskManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryAsk;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->solidaryAskManager->createSolidaryAsk($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
