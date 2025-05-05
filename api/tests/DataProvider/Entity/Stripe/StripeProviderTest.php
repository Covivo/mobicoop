<?php

namespace App\DataProvider\Entity\Stripe;

use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\PaymentResult;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockBankAccount;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockPaymentProfile;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockUser;
use PHPUnit\Framework\TestCase;
use Stripe\Account as StripeAccount;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\Collection;
use Stripe\PaymentLink;
use Stripe\Payout;
use Stripe\Price;
use Stripe\Token as StripeToken;
use Stripe\Transfer;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class StripeProviderTest extends TestCase
{
    private $_stripeProvider;
    private $_paymentProfileRepository;
    private $_clientId = 'clientId';
    private $_apikey = 'sk_test_apikey';
    private $_currency = 'EUR';
    private $_validationDocsPath = 'validationDocsPath';
    private $_baseUri = 'baseUri';
    private $_baseMobileUri = 'baseMobileUri';

    public function setUp(): void
    {
        $this->_paymentProfileRepository = $this->getMockBuilder(PaymentProfileRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_paymentProfileRepository->method('findBy')
            ->willReturn([MockPaymentProfile::getPaymentProfile()])
        ;
        $this->_paymentProfileRepository->method('find')
            ->willReturn(MockPaymentProfile::getPaymentProfile())
        ;

        // Create mock with proper callable method
        $tokensMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock()
        ;
        $tokensMock->method('create')
            ->willReturn(new StripeToken('token'))
        ;

        $accountsMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->addMethods(['createExternalAccount'])
            ->addMethods(['allExternalAccounts'])
            ->getMock()
        ;
        $accountsMock->method('create')
            ->willReturn(new StripeAccount('acct_1232133213'))
        ;
        $accountsMock->method('createExternalAccount')
            ->willReturn(new StripeBankAccount('token'))
        ;

        $stripeBankAccount = new StripeBankAccount('ba_123');
        $stripeBankAccount->metadata = new \stdClass();
        $stripeBankAccount->metadata->status = 'validated';
        $stripeBankAccount->status = 'validated';
        $stripeBankAccount->bank_name = 'Test Bank';
        $stripeBankAccount->last4 = '1234';

        $collection = Collection::constructFrom([
            'object' => 'list',
            'data' => [$stripeBankAccount],
            'has_more' => false,
            'url' => '/v1/accounts/acct_123/external_accounts',
        ]);

        $accountsMock->method('allExternalAccounts')
            ->willReturn($collection)
        ;

        $pricesMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock()
        ;
        $pricesMock->method('create')
            ->willReturn(new Price('price_123'))
        ;

        $paymentLinksMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock()
        ;

        $paymentLink = new PaymentLink('plink_123');
        $paymentLink->url = 'https://yourbusiness.com/test';

        $paymentLinksMock->method('create')
            ->willReturn($paymentLink)
        ;

        $stripeMock = new \stdClass();
        $stripeMock->tokens = $tokensMock;
        $stripeMock->accounts = $accountsMock;
        $stripeMock->prices = $pricesMock;
        $stripeMock->paymentLinks = $paymentLinksMock;

        $this->_stripeProvider = new StripeProvider(
            MockUser::getSimpleUser(),
            $this->_clientId,
            $this->_apikey,
            true,
            $this->_currency,
            $this->_validationDocsPath,
            $this->_baseUri,
            $this->_baseMobileUri,
            $this->_paymentProfileRepository
        );

        // Inject mock
        $reflection = new \ReflectionClass($this->_stripeProvider);
        $property = $reflection->getProperty('_stripe');
        $property->setAccessible(true);
        $property->setValue($this->_stripeProvider, $stripeMock);
    }

    /**
     * @test
     * */
    public function testRegisterUserReturnsAString()
    {
        $this->assertIsString($this->_stripeProvider->registerUser(MockUser::getSimpleUser()));
    }

    /**
     * @test
     */
    public function registerUserReturnsStringStartingWithAcct()
    {
        $result = $this->_stripeProvider->registerUser(MockUser::getSimpleUser());
        $this->assertStringStartsWith('acct_', $result);
    }

    /**
     * @test
     */
    public function addBankAccountReturnsInstanceOfBankAccount()
    {
        $bankAccount = $this->_stripeProvider->addBankAccount(MockBankAccount::getBankAccount(), 'acct_1232133213');
        $this->assertInstanceOf(BankAccount::class, $bankAccount);
    }

    /**
     * @test
     */
    public function invalidApiKeyThrowsException(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Invalid API key');

        new StripeProvider(
            MockUser::getSimpleUser(),
            $this->_clientId,
            'invalidApiKey',
            true,
            $this->_currency,
            $this->_validationDocsPath,
            $this->_baseUri,
            $this->_baseMobileUri,
            $this->_paymentProfileRepository
        );
    }

    /**
     * @test
     */
    public function productionApiKeyThrowsExceptionInSandboxMode(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Invalid API key');

        new StripeProvider(
            MockUser::getSimpleUser(),
            $this->_clientId,
            'sk_live',
            true,
            $this->_currency,
            $this->_validationDocsPath,
            $this->_baseUri,
            $this->_baseMobileUri,
            $this->_paymentProfileRepository
        );
    }

    /**
     * @test
     */
    public function testApiKeyThrowsExceptionInProductionMode(): void
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Invalid API key');

        new StripeProvider(
            MockUser::getSimpleUser(),
            $this->_clientId,
            'sk_test',
            false,
            $this->_currency,
            $this->_validationDocsPath,
            $this->_baseUri,
            $this->_baseMobileUri,
            $this->_paymentProfileRepository
        );
    }

    /**
     * @test
     */
    public function generateElectronicPaymentUrlReturnsACarpoolPaymentWithAnUrl(): void
    {
        $carpoolPayment = new CarpoolPayment();
        $user = MockUser::getSimpleUser();
        $user->addPaymentProfile(MockPaymentProfile::getPaymentProfile());
        $carpoolPayment->setUser($user);
        $carpoolPayment->setAmountOnline(100);

        $payment = $this->_stripeProvider->generateElectronicPaymentUrl($carpoolPayment);

        $this->assertInstanceOf(CarpoolPayment::class, $payment);
        $this->assertStringStartsWith('https://', $payment->getRedirectUrl());
    }

    /**
     * @test
     */
    public function getBankAccountsReturnsAnArrayOfBankAccounts(): void
    {
        $bankAccounts = $this->_stripeProvider->getBankAccounts(MockPaymentProfile::getPaymentProfile());
        $this->assertIsArray($bankAccounts);
        $this->assertInstanceOf(BankAccount::class, $bankAccounts[0]);
    }

    /**
     * @test
     */
    public function processAsyncElectronicPaymentWhenDebtorStatusIsPendingReturnsAnArrayOfPaymentResults(): void
    {
        $debtor = MockUser::getSimpleUser();

        $creditor = [
            'user' => MockUser::getSimpleUser(),
            'amount' => 100,
            'debtorStatus' => CarpoolItem::DEBTOR_STATUS_PENDING_ONLINE,
            'carpoolItemId' => 1,
        ];

        $transfersMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock()
        ;

        $transfersMock->method('create')
            ->willReturn(new Transfer('tr_123'))
        ;

        // Add transfers mock to stripe mock
        $reflection = new \ReflectionClass($this->_stripeProvider);
        $property = $reflection->getProperty('_stripe');
        $property->setAccessible(true);
        $stripeMock = $property->getValue($this->_stripeProvider);
        $stripeMock->transfers = $transfersMock;

        $results = $this->_stripeProvider->processAsyncElectronicPayment($debtor, [$creditor], 1);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(PaymentResult::class, $results[0]);
        $this->assertEquals(PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_TRANSFER, $results[0]->getType());
        $this->assertEquals(PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_SUCCESS, $results[0]->getStatus());
    }

    /**
     * @test
     * */
    /**
     * @test
     */
    public function processAsyncElectronicPaymentWhenDebtorStatusIsOnlineReturnsAnArrayOfPaymentResults(): void
    {
        $debtor = MockUser::getSimpleUser();

        $creditor = [
            'user' => MockUser::getSimpleUser(),
            'amount' => 100,
            'debtorStatus' => CarpoolItem::DEBTOR_STATUS_ONLINE,
            'carpoolItemId' => 1,
        ];

        $payoutsMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock()
        ;

        $payoutsMock->method('create')
            ->willReturn(new Payout('po_123'))
        ;

        // Add payouts mock to stripe mock
        $reflection = new \ReflectionClass($this->_stripeProvider);
        $property = $reflection->getProperty('_stripe');
        $property->setAccessible(true);
        $stripeMock = $property->getValue($this->_stripeProvider);
        $stripeMock->payouts = $payoutsMock;

        $results = $this->_stripeProvider->processAsyncElectronicPayment($debtor, [$creditor], 1);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(PaymentResult::class, $results[0]);
        $this->assertEquals(PaymentResult::RESULT_ONLINE_PAYMENT_TYPE_PAYOUT, $results[0]->getType());
        $this->assertEquals(PaymentResult::RESULT_ONLINE_PAYMENT_STATUS_SUCCESS, $results[0]->getStatus());
    }
}
