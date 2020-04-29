<?php
namespace App\Solidary\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Solidary\Entity\SolidaryTransportersSchedule\SolidaryTransportersSchedule;
use App\Solidary\Service\SolidaryTransportersScheduleManager;

final class SolidaryTransportersScheduleDataPersister implements ContextAwareDataPersisterInterface
{
    private $solidaryTransportersScheduleManager;

    public function __construct(SolidaryTransportersScheduleManager $solidaryTransportersScheduleManager)
    {
        $this->solidaryTransportersScheduleManager = $solidaryTransportersScheduleManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof SolidaryTransportersSchedule;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post') {
            $data = $this->solidaryTransportersScheduleManager->buildSolidaryTransportersSchedule($data);
        }
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
