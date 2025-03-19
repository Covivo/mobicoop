<?php

namespace App\DataProvider\Entity\Stripe;

use App\Payment\Entity\CarpoolPayment;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockBankAccount;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockPaymentProfile;
use App\Tests\DataProvider\Entity\Stripe\Mock\MockUser;
use PHPUnit\Framework\TestCase;
use Stripe\Account as StripeAccount;
use Stripe\BankAccount as StripeBankAccount;
use Stripe\PaymentLink;
use Stripe\Price;
use Stripe\Token as StripeToken;

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
            ->getMock()
        ;
        $accountsMock->method('create')
            ->willReturn(new StripeAccount('acct_1232133213'))
        ;
        $accountsMock->method('createExternalAccount')
            ->willReturn(new StripeBankAccount('token'))
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
}
