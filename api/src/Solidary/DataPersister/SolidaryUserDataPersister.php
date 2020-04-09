<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Service\SolidaryUserManager;

final class SolidaryUserDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryUserManager;
    
    public function __construct(SolidaryUserManager $solidaryUserManager)
    {
        $this->solidaryUserManager = $solidaryUserManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryUser;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'put') {
            $data = $this->solidaryUserManager->updateSolidaryUser($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
