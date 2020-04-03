<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryContact;

final class SolidaryContactDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct()
    {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryContact;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
