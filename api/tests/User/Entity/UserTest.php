<?php

namespace App\User\Entity;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class UserTest extends TestCase
{
    private const DEFAULT_SSO_PROVIDER = OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT;

    /**
     * @var User
     */
    private $_user;

    /**
     * @var SsoAccount
     */
    private $_ssoAccount;

    public function setUp(): void
    {
        $this->_user = new User();

        $this->_ssoAccount = new SsoAccount();
        $this->_ssoAccount->setSsoProvider(self::DEFAULT_SSO_PROVIDER);
    }

    /**
     * @test
     */
    public function isAssociatedWithSsoAccount()
    {
        $this->assertFalse($this->_user->isAssociatedWithSsoAccount(self::DEFAULT_SSO_PROVIDER));

        $this->_user->addSsoAccount($this->_ssoAccount);
        $this->assertTrue($this->_user->isAssociatedWithSsoAccount(self::DEFAULT_SSO_PROVIDER));
    }

    /**
     * @test
     */
    public function getSsoAccountException()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->_user->getSsoAccount(self::DEFAULT_SSO_PROVIDER);
    }

    /**
     * @test
     */
    public function getSsoAccount()
    {
        $this->_user->addSsoAccount($this->_ssoAccount);
        $this->assertInstanceOf('App\User\Entity\SsoAccount', $this->_user->getSsoAccount(self::DEFAULT_SSO_PROVIDER));
    }

    /**
     * @test
     */
    public function isPhoneValidatedFalsy()
    {
        $this->assertFalse($this->_user->isPhoneValidated());

        $this->_user->setTelephone('060102030405');
        $this->assertFalse($this->_user->isPhoneValidated());

        $this->_user->setTelephone(null);
        $this->_user->setPhoneValidatedDate(new \DateTime('now'));
        $this->assertFalse($this->_user->isPhoneValidated());
    }

    /**
     * @test
     */
    public function isPhoneValidatedTruly()
    {
        $this->_user->setTelephone('060102030405');
        $this->_user->setPhoneValidatedDate(new \DateTime('now'));

        $this->assertTrue($this->_user->isPhoneValidated());
    }
}
