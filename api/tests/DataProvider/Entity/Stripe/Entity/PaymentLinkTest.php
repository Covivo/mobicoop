<?php

namespace App\DataProvider\Entity\Stripe\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class PaymentLinkTest extends TestCase
{
    private $_prices;
    private $_paymentLink;
    private $_result;

    public function setUp(): void
    {
        $this->_prices = [
            'price_123456789',
            'price_1011121314151617',
        ];

        $this->_result = [
            'line_items' => [
                [
                    'price' => 'price_123456789',
                    'quantity' => 1,
                ],
                [
                    'price' => 'price_1011121314151617',
                    'quantity' => 1,
                ],
            ],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [
                    'url' => 'https://yourbusiness.com/test',
                ],
            ],
        ];
        $this->_paymentLink = new PaymentLink($this->_prices, 'https://yourbusiness.com/test');
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsAnArray()
    {
        $this->assertIsArray($this->_paymentLink->buildBody());
    }

    /**
     * @test
     */
    public function testBuildBodyReturnsARightArray()
    {
        $this->assertSame($this->_result, $this->_paymentLink->buildBody());
    }

    /**
     * @test
     */
    public function testLineItemsReturnsSameAmountAsPrices()
    {
        $this->assertCount(count($this->_prices), $this->_paymentLink->buildBody()['line_items']);
    }
}
