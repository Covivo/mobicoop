<?php

namespace App\DataProvider\DataPersister;

use App\DataProvider\Ressource\StripeHook;
use App\Payment\Service\PaymentManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class StripeHookDataPersisterTest extends TestCase
{
    private $paymentManager;
    private $requestStack;
    private $security;
    private $logger;
    private $stripeHookDataPersister;
    private $request;

    public function setUp(): void
    {
        $this->paymentManager = $this->createMock(PaymentManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->request = $this->createMock(Request::class);

        // Configure le mock RequestStack pour retourner le mock Request
        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request)
        ;

        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')
            ->with('stripe-signature')
            ->willReturn('some_signature')
        ;
        $this->request->headers = $headers;

        $this->stripeHookDataPersister = new StripeHookDataPersister($this->paymentManager, $this->requestStack, $this->security, $this->logger);
    }

    /**
     * @test
     *
     * @dataProvider getValidatedData
     */
    public function testHandleHookValidationisCalled(string $data, string $stripeEvent)
    {
        $this->request->method('getContent')
            ->willReturn($stripeEvent)
        ;

        $this->paymentManager->expects($this->once())
            ->method('handleHookValidation')
            ->with($this->isInstanceOf(StripeHook::class))
        ;

        $this->stripeHookDataPersister->persist($data, ['operation_name' => $data]);
    }

    /**
     * @test
     *
     * @dataProvider getValidatedData
     */
    public function testHookRessourceIdIsDocumentId(string $data, string $stripeEvent)
    {
        $this->request->method('getContent')
            ->willReturn($stripeEvent)
        ;

        $decodedEvent = json_decode($stripeEvent, true);
        $accountId = $decodedEvent['data']['object']['individual']['verification']['document']['front'];

        $this->paymentManager->expects($this->once())
            ->method('handleHookValidation')
            ->with($this->callback(function ($hook) use ($accountId) {
                // Vérifie que le ressourceId du Hook correspond à l'account du JSON
                return $hook instanceof StripeHook
                    && $hook->getRessourceId() === $accountId;
            }))
        ;

        $this->stripeHookDataPersister->persist($data, ['operation_name' => $data]);
    }

    public function getValidatedData(): array
    {
        $stripeEvent = '{"data":{"object":{"individual":{"account":"accountId","verification":{"document":{"front":"documentId"},"status":"verified"}}}}, "type":"account.updated"}';

        return [
            ['stripe_webhook', $stripeEvent],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getNotTriggeringData
     */
    public function testHandleHookValidationisNotCalled(string $data, string $stripeEvent)
    {
        $this->request->method('getContent')
            ->willReturn($stripeEvent)
        ;

        $this->paymentManager->expects($this->never())
            ->method('handleHookValidation')
        ;

        $this->stripeHookDataPersister->persist($data, ['operation_name' => $data]);
    }

    public function getNotTriggeringData(): array
    {
        $stripeEvent = '{"data":{"object":{"individual":{"verification":{"document":{"front":"documentId"},"status":"pending"}}}}, "type":"account.updated"}';
        $stripeEvent2 = '{"data":{"object":{"individual":{"verification":{"document":{"front":"documentId"},"status":"verified"}}}}, "type":"account.created"}';

        return [
            ['stripe_webhook', $stripeEvent],
            ['stripe_webhook', $stripeEvent2],
        ];
    }
}
