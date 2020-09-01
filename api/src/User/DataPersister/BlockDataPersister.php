<?php
namespace App\User\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\User\Entity\User;
use App\User\Exception\BlockException;
use App\User\Ressource\Block;
use App\User\Service\BlockManager;
use LogicException;
use Symfony\Component\Security\Core\Security;

final class BlockDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $blockManager;
    
    public function __construct(Security $security, BlockManager $blockManager)
    {
        $this->security = $security;
        $this->blockManager = $blockManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Block && isset($context['collection_operation_name']) &&  $context['collection_operation_name'] == 'block';
    }

    public function persist($data, array $context = [])
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new BlockException(BlockException::ONLY_USER_CAN_BLOCK);
        }
        return $this->blockManager->handleBlock($this->security->getUser(), $data->getUser());
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
