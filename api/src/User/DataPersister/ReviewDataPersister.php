<?php
namespace App\User\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\User;
use App\User\Ressource\Review;
use App\User\Service\ReviewManager;
use Symfony\Component\Security\Core\Security;

final class ReviewDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $reviewManager;
    
    public function __construct(Security $security, ReviewManager $reviewManager)
    {
        $this->security = $security;
        $this->reviewManager = $reviewManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Review && isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        return $this->reviewManager->createReview($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
