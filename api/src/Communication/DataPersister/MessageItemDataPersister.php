<?php
namespace App\Communication\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class MessageItemDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $security;
    private $internalMessageManager;

    public function __construct(RequestStack $requestStack, Security $security, InternalMessageManager $internalMessageManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->internalMessageManager = $internalMessageManager;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Message && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad message  id is provided"));
        }
        return $this->internalMessageManager->postMessage($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
