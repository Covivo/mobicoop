<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryContact;
use App\Solidary\Service\SolidaryContactManager;

final class SolidaryContactDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryContactManager;

    public function __construct(SolidaryContactManager $solidaryContactManager)
    {
        $this->solidaryContactManager = $solidaryContactManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryContact;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->solidaryContactManager->handleSolidaryContact($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
