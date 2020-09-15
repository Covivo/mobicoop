<?php


namespace App\DataProvider\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\DataProvider\Ressource\MangoPayIn;
use App\Payment\Service\PaymentDataProvider;
use Symfony\Component\HttpFoundation\RequestStack;

final class MangoPayInCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $paymentDataProvider;

    public function __construct(RequestStack $requestStack, PaymentDataProvider $paymentDataProvider)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->paymentDataProvider = $paymentDataProvider;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MangoPayIn::class === $resourceClass && $operationName == "mangoPayins";
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $mangoPayIn = new MangoPayIn();
        if (is_null($this->request->get('EventType')) ||
            is_null($this->request->get('RessourceId')) ||
            is_null($this->request->get('Date'))
        ) {
            throw new \LogicException("Missing parameter");
        }
        
        if ($this->request->get('EventType')!==MangoPayIn::PAYIN_SUCCEEDED &&
            $this->request->get('EventType')!==MangoPayIn::PAYIN_FAILED
        ) {
            throw new \LogicException("Unknown MangoPay EventType");
        }

        $mangoPayIn->setEventType($this->request->get('EventType'));
        $mangoPayIn->setRessourceId($this->request->get('RessourceId'));
        $mangoPayIn->setDate($this->request->get('Date'));
        return $this->paymentDataProvider->handleHook($mangoPayIn);
    }
}
