<?php

namespace App\Carpool\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AskManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class AdAskItemDataPersister implements ContextAwareDataPersisterInterface
{
    private $askManager;
    private $request;
    private $security;

    public function __construct(AskManager $askManager, RequestStack $requestStack, Security $security)
    {
        $this->askManager = $askManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Ad && isset($context['item_operation_name']) && 'put_ask' == $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad Ad id is provided'));
        }

        return $this->askManager->updateAskFromAd($data, $this->request->get('id'), $this->security->getUser()->getId());
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
