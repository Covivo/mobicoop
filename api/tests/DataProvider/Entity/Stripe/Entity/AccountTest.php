<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;
use Stripe\Token;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class AccountTest extends TestCase
{
    public function setUp(): void {}

    /**
     * @test
     *
     * @dataProvider getData
     */
    public function testBuildBodyReturnsAnArray(Token $accountToken, string $url)
    {
        $accountToken = new Account($accountToken, $url);
        $this->assertIsArray($accountToken->buildBody());
    }

    public function getData(): array
    {
        $token = new Token('acct_XXXXXXXXXXXXX');
        $url = 'https://www.example.com/';

        $result = '{"account_token":"acct_XXXXXXXXXXXXX","business_profile":{"mcc":"4789","url":"https:\/\/www.example.com\/"},"capabilities":{"bank_transfer_payments":{"requested":true},"card_payments":{"requested":true},"transfers":{"requested":true}},"type":"custom"}';

        return [
            [$token, $url, $result],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getData
     */
    public function testBuildBodyReturnsARightArray(Token $accountToken, string $url, string $result)
    {
        $accountToken = new Account($accountToken, $url);
        $this->assertEquals($result, json_encode($accountToken->buildBody()));
    }
}
